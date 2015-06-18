<?hh // strict

namespace kilahm\Clio\Test\Util;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Util\Parser;

class ParserTest
{
    private function buildParser(...) : Parser
    {
        return new Parser(Vector::fromItems(func_get_args())->map($i ==> (string)$i));
    }

    <<Test>>
    public function testParserCreatesArguments(Assert $assert) : void
    {
        $argv = Vector{'one', 'two'};
        $p = new Parser($argv);
        $args = $p->getArguments();
        $assert->mixed($args->map($a ==> $a->getName()))->looselyEquals(Vector{'1', '2'});
        $assert->mixed($args->map($a ==> $a->value()))->looselyEquals($argv);
    }

    <<Test>>
    public function testParserAddsArgumentsAsNeeded(Assert $assert) : void
    {
        $p = $this->buildParser('one', 'two');
        $arg = $p->arg('test');
        $args = $p->getArguments();
        $assert->mixed($args->map($a ==> $a->getName()))->looselyEquals(Vector{'test', '2'});
        $assert->mixed($args->map($a ==> $a->value()))->looselyEquals(Vector{'one', 'two'});
    }

    <<Test>>
    public function testParserEmitsUnknownLongOption(Assert $assert) : void
    {
        $p = $this->buildParser('--unknown');
        $p->onUnknownOption($name ==> {
            throw new \Exception($name);
        });
        $assert->whenCalled(() ==> {
            $p->parse();
        })->willThrowClassWithMessage(\Exception::class, 'unknown');
    }

    <<Test>>
    public function testParserEmitsUnknownBuriedLongOption(Assert $assert) : void
    {
        $p = $this->buildParser('--known', '--unknown', '--known');
        $p->onUnknownOption($name ==> {
            throw new \Exception($name);
        });
        $p->option('known');
        $assert->whenCalled(() ==> {
            $p->parse();
        })->willThrowClassWithMessage(\Exception::class, 'unknown');
    }

    <<Test>>
    public function testParserEmitsUnknownShortOption(Assert $assert) : void
    {
        $p = $this->buildParser('-k');
        $p->onUnknownOption($name ==> {
            throw new \Exception($name);
        });
        $assert->whenCalled(() ==> {
            $p->parse();
        })->willThrowClassWithMessage(\Exception::class, 'k');
    }

    <<Test>>
    public function testParserEmitsUnknownBuriedShortOption(Assert $assert) : void
    {
        $p = $this->buildParser('-aka');
        $p->onUnknownOption($name ==> {
            throw new \Exception(func_get_args()[0]);
        });
        $p->flag('a');
        $assert->whenCalled(() ==> {
            $p->parse();
        })->willThrowClassWithMessage(\Exception::class, 'k');
    }

    <<Test>>
    public function testFlagCount(Assert $assert) : void
    {
        $p = $this->buildParser('-vkv', '-v');
        $flag = $p->flag('v');
        $otherFlag = $p->flag('long');
        $assert->int($flag->occurances())->eq(3);
        $assert->int($otherFlag->occurances())->eq(0);
    }

    <<Test>>
    public function testParserFindsFlagBetweenArguments(Assert $assert) : void
    {
        $p = $this->buildParser('arg1', '--flag-one', 'arg2');
        $f = $p->flag('flag-one');
        $assert->int($p->getArguments()->count())->eq(2);
        $assert->int($f->occurances())->eq(1);
    }

    <<Test>>
    public function testParserFindsArgumentBetweenFlags(Assert $assert) : void
    {
        $p = $this->buildParser('-f', 'arg', '--flag');
        $arg = $p->arg('need-this');
        $assert->string($arg->value())->is('arg');
    }

    <<Test>>
    public function testParserFindsAliasesOfFlag(Assert $assert) : void
    {
        $p = $this->buildParser('-f', '--flag', '-gag');
        $flag = $p->flag('f')->aka('flag')->aka('g');
        $assert->int($flag->occurances())->eq(4);
    }

    <<Test>>
    public function testParserFindsLongOptionValueWithEqualSign(Assert $assert) : void
    {
        $p = $this->buildParser('--option=stuff');
        $o = $p->option('option');
        $assert->string($o->firstValue())->is('stuff');
    }

    <<Test>>
    public function testParserFindsLongOptionValueWithSpace(Assert $assert) : void
    {
        $p = $this->buildParser('--option', 'stuff');
        $o = $p->option('option');
        $assert->string($o->firstValue())->is('stuff');
    }

    <<Test>>
    public function testParserFindsAllLongOptionValues(Assert $assert) : void
    {
        $p = $this->buildParser('--option=stuff', '--option', 'and things');
        $o = $p->option('option');
        $assert->mixed($o->allValues())->looselyEquals(Vector{'stuff', 'and things'});
    }

    <<Test>>
    public function testParserFindsValuelessOption(Assert $assert) : void
    {
        $p = $this->buildParser('-o', '--other');
        $o = $p->option('o')->aka('other');
        $assert->int($o->occurances())->eq(2);
        $assert->string($o->firstValue())->is('');
        $assert->string($o->lastValue())->is('');
    }

    <<Test>>
    public function testErrorEmittedWhenOptionIsMissingRequiredValue(Assert $assert) : void
    {
        $p = $this->buildParser('-o');
        $o = $p->option('o')->withRequiredValue();
        $o->onMissingValue(() ==> {
            throw new \Exception('missing value');
        });
        $assert->whenCalled(() ==> {
            $p->parse();
        })->willThrowClassWithMessage(\Exception::class, 'missing value');
    }
}
