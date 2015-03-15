<?hh // strict

namespace kilahm\Clio;

use kilahm\Clio\Util\Environment as Env;

<<__ConsistentConstruct>>
class Clio
{
    protected bool $customHelp = false;

    /**
     * Factory method for most use cases
     */
    public static function make() : this
    {
        // Gather the argument values from the server superglobal
        // No filtering or sanitizing is performed
        $argv = Env::argvFromServer();

        // Remove the script name from the argument values
        $name = $argv->at(0);
        $argv->removeKey(0);
        return new static(
            $name,
            $argv,
            new Input\StreamReader(STDIN),
            // the STDOUT constant isn't marked as a writable stream?!
            new Output\StreamWriter(fopen('php://stdout', 'w')),
            new Util\Parser($argv),
        );
    }

    public function __construct(
        protected string $scriptname,
        protected Vector<string> $argv,
        protected Input\Reader $in,
        protected Output\Writer $out,
        protected Util\Parser $parser,
    )
    {
        $parser->on('unknown option', (...) ==> {
            $name = func_get_arg(0);
            if(! is_string($name)) {
                throw new \InvalidArgumentException('Unknown option event must pass the name of the unknown option as a string.');
            }
            $this->showHelp('Unknown option: ' . $name);
            exit();
        });
    }

    /**
     * Throw an exception instead of showing cli help.
     * Useful to override the standard help functionality
     */
    public function throwOnHelp() : this
    {
        $this->customHelp = true;
        return $this;
    }

    /**
     * Trigger the built in help (or an exception)
     * with an optional reason for showing the help
     */
    public function showHelp(string $reason) : void
    {
        if($this->customHelp) {
            throw new Exception\CliHelp($reason);
        }
        echo 'Help triggered';
        // Figure out how to display help here
    }

    /**
     * Wrapper for any particular writer instance used
     */
    public function show(string $content) : void
    {
        $this->out->write($content);
    }

    /**
     * Show the string with a newline appended
     */
    public function line(string $content) : void
    {
        $this->out->writeln($content);
    }

    /**
     * Command line arguments and options
     */
    public function arg(string $name) : Args\Argument
    {
        $a = $this->parser->arg($name);
        $a->on('filter error', (...) ==> {
            $args = Vector::fromItems(func_get_args());
            $value = $args->get(0);
            $this->showHelp('"' . $value . '"' . ' is not a valid argument.');
            exit();
        });

        $a->on('missing argument', (...) ==> {
            $arg = func_get_arg(0);
            if( ! ($arg instanceof Args\Argument)) {
                throw new \InvalidArgumentException('missing argument event must pass the argument object.');
            }
            $this->showHelp('Missing argument "' . $arg->getName() . '"');
        });

        return $a;
    }

    public function option(string $name) : Args\Option
    {
        $o = $this->parser->option($name);
        $o->on('filter error', (...) ==> {
            $args = Vector::fromItems(func_get_args());
            $value = $args->get(0);
            $option = $args->get(1);
            if( ! ($option instanceof Args\Option)) {
                throw new \InvalidArgumentException('option filter error event must pass the option object.');
            }
            $this->showHelp('"' . $value . '"' . ' is not a valid value for ' . $option->getName());
            exit();
        });

        $o->on('missing option value', (...) ==> {
            $option = func_get_arg(0);
            if( ! ($option instanceof Args\Option)) {
                throw new \InvalidArgumentException('missing option value event must pass the option object.');
            }
            $this->showHelp('Option ' . $option->getName() . ' requires a value.');
            exit();
        });

        return $o;
    }

    public function flag(string $name) : Args\Flag
    {
        return $this->parser->flag($name);
    }
}
