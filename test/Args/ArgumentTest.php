<?hh // strict

namespace kilahm\Clio\Test\Args;

use kilahm\Clio\Args\Argument;
use HackPack\HackUnit\Contract\Assert;

<<TestSuite>>
class ArgumentTest
{
    <<Test>>
    public function testArgumentTriggersParseEventOnAccess(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->onParse(() ==> {throw new \Exception('Parsing');});
        $assert->whenCalled(() ==> {
            $arg->value();
        })->willThrowClassWithMessage(\Exception::class, 'Parsing');
    }

    <<Test>>
    public function testArgumentFailsToSetOnFailedFilter(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $arg->set('anything');
        $assert->string($arg->value())->is('');
    }

    <<Test>>
    public function testArgumentGivesEmptyStringWhenNotSet(Assert $assert) : void
    {
        $arg = new Argument('test');
        $assert->string($arg->value())->is('');
    }

    <<Test>>
    public function testArgumentTriggersFilterErrorOnFailedFilter(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $testValue = 'test string';
        $arg->onValidationError((string $value) ==> {
            $assert->string($value)->is($testValue);
            throw new \Exception('filtering');
        });
        $assert->whenCalled(() ==> {
            $arg->set($testValue);
        })->willThrowClassWithMessage(\Exception::class, 'filtering');
    }

    <<Test>>
    public function testArgumentSavesValue(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->set('value');
        $assert->string($arg->value())->is('value');
    }

    <<Test>>
    public function testArgumentGivesDefaultWhenNotSet(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default');
        $assert->string($arg->value())->is('default');
    }

    <<Test>>
    public function testArgumentOverridesDefaultWhenSet(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default')->set('value');
        $assert->string($arg->value())->is('value');
    }

    <<Test>>
    public function testArgumentTriggersMissingValueWhenNotSet(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->onMissing(() ==> {
            throw new \Exception('missing argument');
        });

        $assert->whenCalled(() ==> {
            $arg->value();
        })->willThrowClassWithMessage(\Exception::class, 'missing argument');
    }

    <<Test>>
    public function testArgumentTransformsValue(Assert $assert) : void
    {
        $arg = new Argument('test');
        $arg->transformedBy(($in) ==> 'out');
        $arg->set('value');
        $assert->string($arg->value())->is('out');
    }
}
