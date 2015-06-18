<?hh // strict

namespace kilahm\Clio\Test\Output;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Output\StreamWriter;

<<TestSuite>>
class StreamWriterTest
{
    <<Test>>
    public function testWriterWritesCharacter(Assert $assert) : void
    {
        $s = fopen('php://memory', 'w+');
        $w = new StreamWriter($s);
        $w->write('a');
        $assert->string(stream_get_contents($s, 1, 0))->is('a');
        fclose($s);
    }
}
