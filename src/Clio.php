<?hh // decl

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
        Env::cliOrNotFound();
        $argv = Env::argvFromServer();
        $name = $argv->at(0);
        $argv->removeKey(0);
        return new static(
            $name,
            $argv,
            new Input\StreamReader(STDIN),
            new Output\StreamWriter(STDOUT),
            new Output\StreamWriter(STDERR),
            new Util\Parser($argv),
        );
    }

    public function __construct(
        protected string $scriptname,
        protected Vector<string> $argv,
        protected Input\Reader $in,
        protected Output\Writer $out,
        protected Output\Writer $err,
        protected Util\Parser $parser,
    )
    {
        $parser->on('parseError', inst_meth($this, 'handleParseError'));
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

    public function showHelp(?\Exception $e = null) : void
    {
        if($this->customHelp) {
            throw $e;
        }
        echo 'Help triggered';
        // Figure out how to display help here
    }

}
