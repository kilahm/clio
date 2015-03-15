<?hh // strict

namespace kilahm\Clio\Test\Output;

use kilahm\Clio\Output\StreamWriter;

class StreamWriterTest extends \HackPack\HackUnit\Core\TestCase
{
    <<test>>
    public function testWriterWritesCharacter() : void
    {
        $s = fopen('php://memory', 'w+');
        $w = new StreamWriter($s);
        $w->write('a');
        $this->expect(stream_get_contents($s, 1, 0))->toBeIdenticalTo('a');
        fclose($s);
    }
}
