<?hh // strict

namespace kilahm\Clio\Test\Format;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Format\Table;
use kilahm\Clio\Format\Text;

class TableTest
{
    <<__Memoize>>
    protected static function makeData() : Vector<Vector<Text>>
    {
        return Vector{
            Vector{Text::style('head'), Text::style('he')},
            Vector{Text::style('row1a'), Text::style('row1b')},
            Vector{Text::style('row2a'), Text::style('row2b'), Text::style('row2c')},
            Vector{Text::style('row3c')},
        };
    }

    <<__Memoize>>
    protected static function genScreenWidth() : (function():int)
    {
        return () ==> 31;
    }

    protected function makeTable() : Table
    {
        return new Table(self::makeData(), self::genScreenWidth());
    }

    <<Test>>
    public function testDefaultTableHead(Assert $assert) : void
    {
        $expected =
            '+-----------------------------+' . PHP_EOL .
            '|  head   |   he    |         |' . PHP_EOL .
            '+-----------------------------+' . PHP_EOL;
        $t = $this->makeTable();
        $data = self::makeData();
        $assert->string($t->renderHead($data->at(0), $t->size()))->is($expected);
    }

    <<Test>>
    public function testSmallTableHead(Assert $assert) : void
    {
        $expected =
            '+--------+' . PHP_EOL .
            '|he|he|  |' . PHP_EOL .
            '|ad|  |  |' . PHP_EOL .
            '+--------+' . PHP_EOL;
        $t = $this->makeTable();
        $t->withMaxColWidth(2);
        $data = self::makeData();
        $assert->string($t->renderHead($data->at(0), $t->size()))->is($expected);
    }
}
