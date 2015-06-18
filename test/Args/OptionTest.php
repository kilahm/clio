<?hh // strict

namespace kilahm\Clio\Test\Args;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Args\Option;

<<TestSuite>>
class OptionTest
{
    <<Test>>
    public function testOptionTriggersParseOnAccess(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->onParse(() ==> {
            throw new \Exception('parsed');
        });

        $assert->whenCalled(() ==> {
            $opt->firstValue();
        })->willThrowClassWithMessage(\Exception::class, 'parsed');

        $assert->whenCalled(() ==> {
            $opt->lastValue();
        })->willThrowClassWithMessage(\Exception::class, 'parsed');

        $assert->whenCalled(() ==> {
            $opt->allValues();
        })->willThrowClassWithMessage(\Exception::class, 'parsed');

        $assert->whenCalled(() ==> {
            $opt->occurances();
        })->willThrowClassWithMessage(\Exception::class, 'parsed');
    }

    <<Test>>
    public function testOptionSavesAllValues(Assert $assert) : void
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

        $assert->string($opt->firstValue())->is('one');
        $assert->string($opt->lastValue())->is('');
        foreach($opt->allValues() as $idx => $val) {
            $assert->string($val)->is((string)$vals->at($idx));
        }
    }

    <<Test>>
    public function testOptionGivesEmptySetWithDefault(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->withDefault('default');
        $assert->mixed($opt->allValues())->looselyEquals(Vector{});
    }

    <<Test>>
    public function testOptionGivesDefaultWhenNotSet(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->withDefault('default');
        $assert->string($opt->firstValue())->is('default');
    }

    <<Test>>
    public function testOptionGiveValueWithDefault(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->withDefault('default')->set('value');
        $assert->string($opt->firstValue())->is('value');
        $assert->mixed($opt->allValues())->looselyEquals(Vector{'value'});
    }

    <<Test>>
    public function testOptionGivesEmptyStringWhenNotSetWithNoDefault(Assert $assert) : void
    {
        $opt = new Option('test');
        $assert->string($opt->firstValue())->is('');
        $assert->mixed($opt->allValues())->looselyEquals(Vector{});
    }

    <<Test>>
    public function testOptionOccuranceCount(Assert $assert) : void
    {
        $opt = new Option('test');
        $assert->int($opt->occurances())->eq(0);
        $opt->set('one');
        $assert->int($opt->occurances())->eq(1);
        $opt->set('');
        $assert->int($opt->occurances())->eq(2);
        $opt->set(null);
        $assert->int($opt->occurances())->eq(3);
    }

    <<Test>>
    public function testOptionTransformsValue(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->transformedBy(($in) ==> {
            static $count = 0;
            $count++;
            return 'other' . $count;
        });
        $opt->set('val');
        $opt->set('val');
        $assert->mixed($opt->allValues())->looselyEquals(Vector{'other1', 'other2'});
    }

    <<Test>>
    public function testOptionTriggersMissingValueErrorWhenValueIsRequired(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->withRequiredValue();
        $opt->onMissingValue(() ==> {
            throw new \Exception('missing value');
        });
        $assert->whenCalled(() ==> {
            $opt->set(null);
        })->willThrowClassWithMessage(\Exception::class, 'missing value');
        $assert->whenCalled(() ==> {
            $opt->set('');
        })->willThrowClassWithMessage(\Exception::class, 'missing value');
    }

    <<Test>>
    public function testOptionDoesNotTriggerMissingValueErrorWhenNotRequired(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->onMissingValue(() ==> {
            throw new \Exception('missing value');
        });
        $assert->whenCalled(() ==> {
            $opt->set(null);
        })->willNotThrow();
        $assert->whenCalled(() ==> {
            $opt->set('');
        })->willNotThrow();
    }

    <<Test>>
    public function testOptionCanBeCleared(Assert $assert) : void
    {
        $opt = new Option('test');
        $opt->set('test');
        $assert->int($opt->occurances())->eq(1);
        $assert->string($opt->firstValue())->is('test');
        $opt->reset();
        $assert->int($opt->occurances())->eq(0);
        $assert->string($opt->firstValue())->is('');
    }
}
