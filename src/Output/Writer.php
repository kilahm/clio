<?hh // strict

namespace kilahm\Clio\Output;

interface Writer
{
    /**
     * Send the string to the receiver.
     *
     * @throws WriteError when an error occurs while writing the string
     */
    public function write(string $content) : void;

    /**
     * Send the string to the receiver with a newline appended.
     */
    public function writeln(string $content) : void;
}
