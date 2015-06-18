<?hh // strict

namespace kilahm\Clio\Test\Format;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\Format\DefinitionList;

<<TestSuite>>
class DefinitionListTest
{
    private static Map<string,string> $data = Map{
        'Item 1' => 'Describes item 1.  Item one is the most A+ type item!',
        'Item B' => 'Meh...',
        'Third Item' => '3 lord.',
        'Item the fourth' => '4 shalt thou not count.  5 is right out!',
    };

    <<Test>>
    public function testDefListWillRender(Assert $assert) : void
    {
        $assert->whenCalled(() ==> {
            DefinitionList::make(self::$data)->render();
        })->willNotThrow();
    }

    <<Test>>
    public function testDefListContainsAllData(Assert $assert) : void
    {
        $list = DefinitionList::make(self::$data)->render();
        foreach(self::$data as $term => $description) {
            $assert->string($list)->matches(sprintf('#%s#', preg_quote($term)));
            $assert->string($list)->matches(sprintf('#%s#', preg_quote($description)));
        }
    }
}
