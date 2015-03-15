<?hh // strict

namespace kilahm\Clio\Test\Input;

use kilahm\Clio\Exception\ReadError;
use kilahm\Clio\Input\StreamReader;

class StreamReaderTest extends \HackPack\HackUnit\Core\TestCase
{
    private array<int,resource> $pipes = [];
    private ?resource $proc = null;

    //<<test>>
    //public function testReaderNeedsReadableStream() : void
    //{
        //$write = fopen('php://temp', 'w');
        //$read = fopen('php://temp', 'r');
        //$this->expectCallable(() ==> {
            //$reader = new StreamReader($write);
            //$reader->getChar();
        //})->toThrow(ReadError::class);
        //$this->expectCallable(() ==> {
            //$reader = new StreamReader($read);
            //$reader->getChar();
        //})->toNotThrow();
        //fclose($write);
        //fclose($read);
    //}

    <<test>>
    public function testReaderWaitsForLine() : void
    {
        $reader = new StreamReader(
            $this->startProc('printf a;sleep 0.01;printf "\n"')
        );
        $this->expect($reader->waitForLine())->toEqual("a");
        $this->closeStreams();
    }

    private function startProc(string $command) : resource
    {
        $this->closeStreams();
        $this->pipes = [];
        $this->proc = proc_open(
            $command,
            [
                1 => ['pipe', 'w']
            ],
            $this->pipes,
        );

        $pipe = $this->pipes[1];

        if(is_resource($pipe)) {
            return $pipe;
        }
        throw new \Exception('Unable to start the proc');
    }

    private function closeStreams() : void
    {
        array_walk($this->pipes, $p ==> {
            if(is_resource($p)){
                fclose($p);
            }
        });

        if(is_resource($this->proc)) {
            proc_close($this->proc);
        }
    }

    /**
     * Make sure the streams are closed before the process
     */
    public function __destruct()
    {
        $this->closeStreams();
    }
}
