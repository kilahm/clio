<?hh // strict

namespace kilahm\Clio\Input;

interface Reader
{
    /**
     * Read from the source until a newline appears.
     * If eof is reached before a newline, return as much content as possible (which could be an empty string)
     * The end of the string MUST NOT include the trailing newline character.
     *
     * @throws ReadError
     */
    public function getLine() : string;

    /**
     * Read from the source until a newline appears.
     * If eof is reached before a newline, wait on the source until one becomes avaiable.
     * The end of the string MUST NOT include the trailing newline character.
     *
     * @throws ReadError
     */
    public function waitForLine() : string;

    /**
     * Return the next character from the source, if available.  If not, return an empty string.
     *
     * @throws ReadError
     */
    public function getChar() : string;

    /**
     * Return the next character from the source, waiting for one if required.
     * This should always return a string of length one character.
     *
     * @throws ReadError
     */
    public function waitForChar() : string;

    /**
     * Return as much content as possible from the source.
     *
     * @throws ReadError
     */
    public function getContents() : string;
}
