<?hh // strict

namespace kilahm\Clio\Test\Args;

use kilahm\Clio\Args\Flag;

class FlagTest extends \HackPack\HackUnit\Core\TestCase
{
    public function testFlagTracksOccurances() : void
    {
        $f = new Flag('test');
        foreach(range(0,5) as $i) {
            $this->expect($f->occurances())->toEqual($i);
            $f->found();
        }
    }

    public function testFlagCanBeReset() : void
    {
        $f = new Flag('test');
        $f->found();
        $f->found();
        $this->expect($f->occurances())->toEqual(2);
        $f->reset();
        $this->expect($f->occurances())->toEqual(0);
    }

    public function testFlagTriggersParseOnAccess() : void
    {
        $f = new Flag('test');
        $f->on('parse', (...) ==> {
            throw new \Exception('parsed');
        });

        $this->expectCallable(() ==> {
            $f->occurances();
        })->toThrow(\Exception::class, 'parsed');
    }
}
