<?hh // strict

namespace kilahm\Clio\Test\Input;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Exception\ReadError;
use kilahm\Clio\Input\StreamReader;

class StreamReaderTest
{
    private array<int,resource> $pipes = [];
    private ?resource $proc = null;

    <<Test>>
    public function testReaderNeedsReadableStream(Assert $assert) : void
    {
        $write = fopen('php://temp', 'w');
        file_put_contents('/tmp/clio.read', 'test string');
        $read = fopen('/tmp/clio.read', 'r');
        $assert->whenCalled(() ==> {
            $reader = new StreamReader($write);
            $reader->getChar();
        })->willThrowClass(ReadError::class);
        $assert->whenCalled(() ==> {
            $reader = new StreamReader($read);
            $reader->getChar();
        })->willNotThrow();
        fclose($write);
        fclose($read);
    }

    <<Test>>
    public function testReaderWaitsForLine(Assert $assert) : void
    {
        $reader = new StreamReader(
            $this->startProc('printf a;sleep 0.01;printf "\n"')
        );
        $assert->string($reader->waitForLine())->is("a");
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
