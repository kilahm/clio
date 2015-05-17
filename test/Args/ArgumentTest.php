<?hh // strict

namespace kilahm\Clio\Test\Args;

use kilahm\Clio\Args\Argument;

class ArgumentTest extends \HackPack\HackUnit\Core\TestCase
{
    <<test>>
    public function testArgumentTriggersParseEventOnAccess() : void
    {
        $arg = new Argument('test');
        $arg->onParse(() ==> {throw new \Exception('Parsing');});
        $this->expectCallable(() ==> {
            $arg->value();
        })->toThrow(\Exception::class, 'Parsing');
    }

    <<test>>
    public function testArgumentFailsToSetOnFailedFilter() : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $arg->set('anything');
        $this->expect($arg->value())->toBeIdenticalTo('');
    }

    <<test>>
    public function testArgumentGivesEmptyStringWhenNotSet() : void
    {
        $arg = new Argument('test');
        $this->expect($arg->value())->toBeIdenticalTo('');
    }

    <<test>>
    public function testArgumentTriggersFilterErrorOnFailedFilter() : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $testValue = 'test string';
        $arg->onValidationError((string $value) ==> {
            $this->expect($value)->toEqual($testValue);
            throw new \Exception('filtering');
        });
        $this->expectCallable(() ==> {
            $arg->set($testValue);
        })->toThrow(\Exception::class, 'filtering');
    }

    <<test>>
    public function testArgumentSavesValue() : void
    {
        $arg = new Argument('test');
        $arg->set('value');
        $this->expect($arg->value())->toEqual('value');
    }

    <<test>>
    public function testArgumentGivesDefaultWhenNotSet() : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default');
        $this->expect($arg->value())->toEqual('default');
    }

    <<test>>
    public function testArgumentOverridesDefaultWhenSet() : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default')->set('value');
        $this->expect($arg->value())->toEqual('value');
    }

    <<test>>
    public function testArgumentTriggersMissingValueWhenNotSet() : void
    {
        $arg = new Argument('test');
        $arg->onMissing(() ==> {
            throw new \Exception('missing argument');
        });

        $this->expectCallable(() ==> {
            $arg->value();
        })->toThrow(\Exception::class, 'missing argument');
    }

    <<test>>
    public function testArgumentTransformsValue() : void
    {
        $arg = new Argument('test');
        $arg->transformedBy(($in) ==> 'out');
        $arg->set('value');
        $this->expect($arg->value())->toBeIdenticalTo('out');
    }
}
