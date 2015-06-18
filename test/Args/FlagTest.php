<?hh // strict

namespace kilahm\Clio\Test\Args;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Args\Flag;

<<TestSuite>>
class FlagTest
{
    <<Test>>
    public function testFlagTracksOccurances(Assert $assert) : void
    {
        $f = new Flag('test');
        foreach(range(0,5) as $i) {
            $assert->int($f->occurances())->eq($i);
            $f->found();
        }
    }

    <<Test>>
    public function testFlagCanBeReset(Assert $assert) : void
    {
        $f = new Flag('test');
        $f->found();
        $f->found();
        $assert->int($f->occurances())->eq(2);
        $f->reset();
        $assert->int($f->occurances())->eq(0);
    }

    <<Test>>
    public function testFlagTriggersParseOnAccess(Assert $assert) : void
    {
        $f = new Flag('test');
        $f->onParse(() ==> {
            throw new \Exception('parsed');
        });

        $assert->whenCalled(() ==> {
            $f->occurances();
        })->willThrowClassWithMessage(\Exception::class, 'parsed');
    }
}
