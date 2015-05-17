<?hh // strict

namespace kilahm\Clio\Test\Args;

use kilahm\Clio\Args\Option;

class OptionTest extends \HackPack\HackUnit\Core\TestCase
{
    public function testOptionTriggersParseOnAccess() : void
    {
        $opt = new Option('test');
        $opt->onParse(() ==> {
            throw new \Exception('parsed');
        });

        $this->expectCallable(() ==> {
            $opt->firstValue();
        })->toThrow(\Exception::class, 'parsed');

        $this->expectCallable(() ==> {
            $opt->lastValue();
        })->toThrow(\Exception::class, 'parsed');

        $this->expectCallable(() ==> {
            $opt->allValues();
        })->toThrow(\Exception::class, 'parsed');

        $this->expectCallable(() ==> {
            $opt->occurances();
        })->toThrow(\Exception::class, 'parsed');
    }

    public function testOptionSavesAllValues() : void
    {
        $opt = new Option('test');
        $vals = Vector{
            'one',
            'two',
            'three',
            '',
            null
        };
        foreach($vals as $val) {
            $opt->set($val);
        }

        $this->expect($opt->firstValue())->toEqual('one');
        $this->expect($opt->lastValue())->toEqual('');
        $this->expect($opt->allValues())->toEqual($vals);
    }

    public function testOptionGivesEmptySetWithDefault() : void
    {
        $opt = new Option('test');
        $opt->withDefault('default');
        $this->expect($opt->allValues())->toEqual(Vector{});
    }

    public function testOptionGivesDefaultWhenNotSet() : void
    {
        $opt = new Option('test');
        $opt->withDefault('default');
        $this->expect($opt->firstValue())->toEqual('default');
    }

    public function testOptionGiveValueWithDefault() : void
    {
        $opt = new Option('test');
        $opt->withDefault('default')->set('value');
        $this->expect($opt->firstValue())->toEqual('value');
        $this->expect($opt->allValues())->toEqual(Vector{'value'});
    }

    public function testOptionGivesEmptyStringWhenNotSetWithNoDefault() : void
    {
        $opt = new Option('test');
        $this->expect($opt->firstValue())->toBeIdenticalTo('');
        $this->expect($opt->allValues())->toEqual(Vector{});
    }

    public function testOptionOccuranceCount() : void
    {
        $opt = new Option('test');
        $this->expect($opt->occurances())->toEqual(0);
        $opt->set('one');
        $this->expect($opt->occurances())->toEqual(1);
        $opt->set('');
        $this->expect($opt->occurances())->toEqual(2);
        $opt->set(null);
        $this->expect($opt->occurances())->toEqual(3);
    }

    public function testOptionTransformsValue() : void
    {
        $opt = new Option('test');
        $opt->transformedBy(($in) ==> {
            static $count = 0;
            $count++;
            return 'other' . $count;
        });
        $opt->set('val');
        $opt->set('val');
        $this->expect($opt->allValues())->toEqual(Vector{'other1', 'other2'});
    }

    public function testOptionTriggersMissingValueErrorWhenValueIsRequired() : void
    {
        $opt = new Option('test');
        $opt->withRequiredValue();
        $opt->onMissingValue(() ==> {
            throw new \Exception('missing value');
        });
        $this->expectCallable(() ==> {
            $opt->set(null);
        })->toThrow(\Exception::class, 'missing value');
        $this->expectCallable(() ==> {
            $opt->set('');
        })->toThrow(\Exception::class, 'missing value');
    }

    public function testOptionDoesNotTriggerMissingValueErrorWhenNotRequired() : void
    {
        $opt = new Option('test');
        $opt->onMissingValue(() ==> {
            throw new \Exception('missing value');
        });
        $this->expectCallable(() ==> {
            $opt->set(null);
        })->toNotThrow();
        $this->expectCallable(() ==> {
            $opt->set('');
        })->toNotThrow();
    }

    public function testOptionCanBeCleared() : void
    {
        $opt = new Option('test');
        $opt->set('test');
        $this->expect($opt->occurances())->toEqual(1);
        $this->expect($opt->firstValue())->toEqual('test');
        $opt->reset();
        $this->expect($opt->occurances())->toEqual(0);
        $this->expect($opt->firstValue())->toEqual('');
    }
}
