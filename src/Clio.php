<?hh // strict

namespace kilahm\Clio;

use kilahm\Clio\Util\Environment as Env;

<<__ConsistentConstruct>>
class Clio
{
    protected bool $customHelp = false;
    protected Vector<Args\Argument> $args = Vector{};
    protected Vector<Args\Opt> $options = Vector{};

    /**
     * Factory method for most use cases
     */
    public static function fromCli() : this
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
            new Input\StreamReader(fopen('php://stdin', 'r')),
            // the STDOUT constant isn't marked as a writable stream?!
            new Output\StreamWriter(fopen('php://stdout', 'w')),
            new Util\Parser($argv),
            new Format\Help($name),
        );
    }

    public function __construct(
        protected string $scriptname,
        protected Vector<string> $argv,
        protected Input\Reader $in,
        protected Output\Writer $out,
        protected Util\Parser $parser,
        protected Format\Help $help,
    )
    {
        $parser->onUnknownOption((string $name) ==> {
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
    public function showHelp(string $reason = '') : void
    {
        if($this->customHelp) {
            throw new Exception\CliHelp($reason);
        }
        $this->out->writeln($this->help->render());
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
    public function allArguments() : Map<string,string>
    {
        return Map::fromItems(
            $this->parser->getArguments()->map(
                $arg ==> Pair{$arg->getName(), $arg->value()}
            )
        );
    }

    public function arg(string $name) : Args\Argument
    {
        $a = $this->parser->arg($name);
        $this->help->addArg($a);
        $a->onValidationError((string $value) ==> {
            $this->showHelp('"' . $value . '" is not a valid argument.');
            exit();
        });

        $a->onMissing(() ==> {
            $this->showHelp('Missing argument "' . $a->getName() . '"');
        });

        return $a;
    }

    public function option(string $name) : Args\Option
    {
        $o = $this->parser->option($name);
        $this->help->addOption($o);
        $o->onValidationError((string $value) ==> {
            $this->showHelp('"' . $value . '"' . ' is not a valid value for ' . $o->getName());
            exit();
        });

        $o->onMissingValue(() ==> {
            $this->showHelp('Option ' . $o->getName() . ' requires a value.');
            exit();
        });

        return $o;
    }

    public function flag(string $name) : Args\Flag
    {
        $f = $this->parser->flag($name);
        $this->help->addFlag($f);
        return $f;
    }

    /**
     * Format objects
     */
    public function table(Traversable<Traversable<string>> $data) : Format\Table
    {
        return Format\Table::fromStrings($data);
    }

    public function style(string $body) : Format\Text
    {
        return Format\Text::style($body);
    }

    public function definitionList(?KeyedIterable<string,string> $data = null) : Format\DefinitionList
    {
        return Format\DefinitionList::make($data);
    }
}
