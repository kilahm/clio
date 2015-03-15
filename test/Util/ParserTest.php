<?hh // strict

namespace kilahm\Clio\Test\Util;

use kilahm\Clio\Util\Parser;

class ParserTest extends \HackPack\HackUnit\Core\TestCase
{
    private function buildParser(...) : Parser
    {
        return new Parser(Vector::fromItems(func_get_args())->map($i ==> (string)$i));
    }

    public function testParserCreatesArguments() : void
    {
        $argv = Vector{'one', 'two'};
        $p = new Parser($argv);
        $args = $p->getArguments();
        $this->expect($args->map($a ==> $a->getName()))->toEqual(Vector{'1', '2'});
        $this->expect($args->map($a ==> $a->value()))->toEqual($argv);
    }

    public function testParserAddsArgumentsAsNeeded() : void
    {
        $argv = Vector{'one', 'two'};
        $p = new Parser($argv);
        $arg = $p->arg('test');
        $args = $p->getArguments();
        $this->expect($args->map($a ==> $a->getName()))->toEqual(Vector{'test', '2'});
        $this->expect($args->map($a ==> $a->value()))->toEqual($argv);
    }

    public function testParserEmitsUnknownLongOption() : void
    {
        $p = $this->buildParser('--unknown');
        $p->on('unknown option', (...) ==> {
            throw new \Exception(func_get_args()[0]);
        });
        $this->expectCallable(() ==> {
            $p->parse();
        })->toThrow(\Exception::class, 'unknown');
    }

    public function testParserEmitsUnknownBuriedLongOption() : void
    {
        $p = $this->buildParser('--known', '--unknown', '--known');
        $p->on('unknown option', (...) ==> {
            throw new \Exception(func_get_args()[0]);
        });
        $p->option('known');
        $this->expectCallable(() ==> {
            $p->parse();
        })->toThrow(\Exception::class, 'unknown');
    }

    public function testParserEmitsUnknownShortOption() : void
    {
        $p = $this->buildParser('-k');
        $p->on('unknown option', (...) ==> {
            throw new \Exception(func_get_args()[0]);
        });
        $this->expectCallable(() ==> {
            $p->parse();
        })->toThrow(\Exception::class, 'k');
    }

    public function testParserEmitsUnknownBuriedShortOption() : void
    {
        $p = $this->buildParser('-aka');
        $p->on('unknown option', (...) ==> {
            throw new \Exception(func_get_args()[0]);
        });
        $p->flag('a');
        $this->expectCallable(() ==> {
            $p->parse();
        })->toThrow(\Exception::class, 'k');
    }

    public function testFlagCount() : void
    {
        $p = $this->buildParser('-vkv', '-v');
        $flag = $p->flag('v');
        $otherFlag = $p->flag('long');
        $this->expect($flag->occurances())->toEqual(3);
        $this->expect($otherFlag->occurances())->toEqual(0);
    }

    public function testParserFindsFlagBetweenArguments() : void
    {
        $p = $this->buildParser('arg1', '--flag-one', 'arg2');
        $f = $p->flag('flag-one');
        $this->expect($p->getArguments()->count())->toEqual(2);
        $this->expect($f->occurances())->toEqual(1);
    }

    public function testParserFindsArgumentBetweenFlags() : void
    {
        $p = $this->buildParser('-f', 'arg', '--flag');
        $arg = $p->arg('need-this');
        $this->expect($arg->value())->toEqual('arg');
    }

    public function testParserFindsAliasesOfFlag() : void
    {
        $p = $this->buildParser('-f', '--flag', '-gag');
        $flag = $p->flag('f')->aka('flag')->aka('g');
        $this->expect($flag->occurances())->toEqual(4);
    }

    public function testParserFindsLongOptionValueWithEqualSign() : void
    {
        $p = $this->buildParser('--option=stuff');
        $o = $p->option('option');
        $this->expect($o->firstValue())->toEqual('stuff');
    }

    public function testParserFindsLongOptionValueWithSpace() : void
    {
        $p = $this->buildParser('--option', 'stuff');
        $o = $p->option('option');
        $this->expect($o->firstValue())->toEqual('stuff');
    }

    public function testParserFindsAllLongOptionValues() : void
    {
        $p = $this->buildParser('--option=stuff', '--option', 'and things');
        $o = $p->option('option');
        $this->expect($o->allValues())->toEqual(Vector{'stuff', 'and things'});
    }

    public function testParserFindsValuelessOption() : void
    {
        $p = $this->buildParser('-o', '--other');
        $o = $p->option('o')->aka('other');
        $this->expect($o->occurances())->toEqual(2);
        $this->expect($o->firstValue())->toBeIdenticalTo('');
        $this->expect($o->lastValue())->toBeIdenticalTo('');
    }

    public function testErrorEmittedWhenOptionIsMissingRequiredValue() : void
    {
        $p = $this->buildParser('-o');
        $o = $p->option('o')->withRequiredValue();
        $o->on('missing option value', (...) ==> {
            throw new \Exception('missing value');
        });
        $this->expectCallable(() ==> {
            $p->parse();
        })->toThrow(\Exception::class, 'missing value');
    }
}
