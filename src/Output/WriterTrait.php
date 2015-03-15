<?hh // strict

namespace kilahm\Clio\Output;

trait WriterTrait
{
    require implements Writer;

    /**
     * Send the string to the receiver with a newline appended.
     */
    public function writeln(string $content) : void
    {
        $this->write($content . PHP_EOL);
    }
}
