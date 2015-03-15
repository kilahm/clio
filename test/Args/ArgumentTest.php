<?hh // strict

namespace kilahm\Clio\Test\Args;

use kilahm\Clio\Args\Argument;

class ArgumentTest extends \HackPack\HackUnit\Core\TestCase
{
    public function testArgumentTriggersParseEventOnAccess() : void
    {
        $arg = new Argument('test');
        $arg->on('parse', (...) ==> {throw new \Exception('Parsing');});
        $this->expectCallable(() ==> {
            $arg->value();
        })->toThrow(\Exception::class, 'Parsing');
    }

    public function testArgumentFailsToSetOnFailedFilter() : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $arg->set('anything');
        $this->expect($arg->value())->toBeIdenticalTo('');
    }

    public function testArgumentGivesEmptyStringWhenNotSet() : void
    {
        $arg = new Argument('test');
        $this->expect($arg->value())->toBeIdenticalTo('');
    }

    public function testArgumentTriggersFilterErrorOnFailedFilter() : void
    {
        $arg = new Argument('test');
        $arg->filteredBy(($val) ==> false);
        $testValue = 'test string';
        $arg->on('filter error', (...) ==> {
            $args = Vector::fromItems(func_get_args());
            $this->expect($args->count())->toEqual(2);
            $this->expect($args->get(0))->toBeIdenticalTo($testValue);
            $this->expect($args->get(1))->toBeIdenticalTo($arg);
        });
        $this->expectCallable(() ==> {
            $arg->set($testValue);
        })->toNotThrow();
    }

    public function testArgumentSavesValue() : void
    {
        $arg = new Argument('test');
        $arg->set('value');
        $this->expect($arg->value())->toEqual('value');
    }

    public function testArgumentGivesDefaultWhenNotSet() : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default');
        $this->expect($arg->value())->toEqual('default');
    }

    public function testArgumentOverridesDefaultWhenSet() : void
    {
        $arg = new Argument('test');
        $arg->withDefault('default')->set('value');
        $this->expect($arg->value())->toEqual('value');
    }

    public function testArgumentTriggersMissingValueWhenNotSet() : void
    {
        $arg = new Argument('test');
        $arg->on('missing argument', (...) ==> {
            throw new \Exception('missing argument');
        });

        $this->expectCallable(() ==> {
            $arg->value();
        })->toThrow(\Exception::class, 'missing argument');
    }

    public function testArgumentTransformsValue() : void
    {
        $arg = new Argument('test');
        $arg->transformedBy(($in) ==> 'out');
        $arg->set('value');
        $this->expect($arg->value())->toBeIdenticalTo('out');
    }
}
