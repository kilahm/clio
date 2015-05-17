<?hh // strict

namespace kilahm\Clio\Util;

use kilahm\Clio\Args\Argument;
use kilahm\Clio\Args\Flag;
use kilahm\Clio\Args\Opt;
use kilahm\Clio\Args\Option;


class Parser
{
    protected Map<string, Opt> $options = Map{};
    protected Vector<Argument> $arguments = Vector{};
    protected Vector<Argument> $parsedArguments = Vector{};
    protected Vector<(function(string):void)> $unknownOptionListeners = Vector{};

    private bool $skipParsing = false;

    public function __construct(protected Vector<string> $argv)
    {
    }

    public function onUnknownOption((function(string):void) $listener) : this
    {
        $this->unknownOptionListeners->add($listener);
        return $this;
    }

    public function triggerUnknownOption(string $name) : void
    {
        foreach($this->unknownOptionListeners as $l) {
            $l($name);
        }
    }

    public function getArguments() : Vector<Argument>
    {
        $this->parse();
        return $this->parsedArguments;
    }

    public function arg(string $name) : Argument
    {
        $this->validateName($name);
        $a = new Argument($name);
        $a->onParse(inst_meth($this, 'parse'));
        $this->arguments->add($a);
        return $a;
    }

    public function option(string $name) : Option
    {
        $this->validateName($name);
        $o = new Option($name);
        $this->registerOpt($o);
        return $o;
    }

    public function flag(string $name) : Flag
    {
        $this->validateName($name);
        $f = new Flag($name);
        $this->registerOpt($f);
        return $f;
    }

    private function registerOpt(Opt $opt) : void
    {
        $this->skipParsing = false;
        $opt
            ->onParse(inst_meth($this, 'parse'))
            ->onAliasAddition((string $newAlias) ==> {
                $this->options->set($newAlias, $opt);
            });
        $this->options->set($opt->getName(), $opt);
    }

    public function parse() : void
    {
        if($this->skipParsing) {
            return;
        }
        $this->skipParsing = true;

        // Reset everything
        $this->parsedArguments->clear();
        foreach($this->options as $option) {
            $option->reset();
        }

        // Copy and reverse to use pop
        $argv = $this->argv->toVector();
        $argv->reverse();
        while(! $argv->isEmpty()) {
            $argText = $argv->pop();
            if(substr($argText, 0, 2) === '--'){
                $this->processLongOpt($argText, $argv);
            } elseif(substr($argText, 0, 1) === '-') {
                $this->processShortOpt($argText, $argv);
            } else {
                $this->processArg($argText);
            }
        }
    }

    private function processArg(string $argText) : void
    {
        $arg = $this->arguments->get($this->parsedArguments->count());
        if($arg === null) {
            $arg = new Argument((string)($this->parsedArguments->count() + 1));
        }
        $arg->set($argText);
        $this->parsedArguments->add($arg);
    }

    private function processLongOpt(string $argText, Vector<string> $argv) : void
    {
        $parts = Vector::fromItems(explode('=', $argText, 2));
        $name = substr($parts->get(0), 2);
        if($name === false) {
            return;
        }

        $opt = $this->options->get($name);
        if($opt instanceof Option) {
            // Use the part after '=' or the next argument
            $opt->set($parts->get(1) === null ? $this->getNextArgument($argv) : $parts->at(1));
        } elseif($opt instanceof Flag) {
            $opt->found();
        } elseif($opt === null) {
            $this->triggerUnknownOption($name);
        }
    }

    private function processShortOpt(string $argText, Vector<string> $argv) : void
    {
        // Loop through all characters, ignoring the leading dash
        for($i = 1; $i < strlen($argText); $i++) {
            $name = substr($argText, $i, 1);
            $opt = $this->options->get($name);

            if($opt instanceof Option) {

                // Try the rest of the argument text
                $val = substr($argText, $i + 1);
                // Or get the next argument
                $opt->set($val === false ? $this->getNextArgument($argv) : $val);

                // Stop looping through the characters
                break;

            } elseif($opt instanceof Flag) {
                $opt->found();
            } elseif($opt === null) {
                $this->triggerUnknownOption($name);
            }
        }
    }

    private function getNextArgument(Vector<string> $argv) : ?string
    {
        if($argv) {
            $next = $argv->pop();

            // Add it back to the list if it's another option
            if(substr($next,0,1) === '-') {
                $argv->add($next);
                return null;
            }

            return $next;
        }
        return null;
    }

    private function validateName(string $name) : void
    {
        if(preg_match('/^[a-zA-Z0-9-]+$/', $name)) {
            return;
        }
        throw new \InvalidArgumentException('Argument and option names may only be alpha-numeric or the hyphen character.');
    }
}
