<?hh // strict

namespace kilahm\Clio\Test\Format;

use kilahm\Clio\Format\DefinitionList;

class DefinitionListTest extends \HackPack\HackUnit\Core\TestCase
{
    private static Map<string,string> $data = Map{
        'Item 1' => 'Describes item 1.  Item one is the most A+ type item!',
        'Item B' => 'Meh...',
        'Third Item' => '3 lord.',
        'Item the fourth' => '4 shalt thou not count.  5 is right out!',
    };

    public function testDefListWillRender() : void
    {
        $this->expectCallable(() ==> {
            DefinitionList::make(self::$data)->render();
        })->toNotThrow();
    }

    public function testDefListContainsAllData() : void
    {
        $list = DefinitionList::make(self::$data)->render();
        foreach(self::$data as $term => $description) {
            $this->expect($list)->toMatch(sprintf('#%s#', $term));
            $this->expect($list)->toMatch(sprintf('#%s#', $description));
        }
    }
}
