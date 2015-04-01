<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\Format\Style;
use kilahm\Clio\Format\Text;
use kilahm\Clio\Util\Environment;

<<__ConsistentConstruct>>
class DefinitionList
{
    private Map<string,string> $definitions = Map{};
    private int $termWidth = 0;
    private int $definitionWidth = 0;
    private StyleGroup $termStyle;
    private StyleGroup $definitionStyle;

    public static function make(?KeyedIterable<string,string> $data = null) : this
    {
        $dl = new static(() ==> Environment::screenSize()['width']);
        if($data !== null) {
            $dl->withDefinitions($data);
        }
        return $dl;
    }

    public function __construct(private (function():int) $screenWidthFetcher)
    {
        $this->termStyle = Style::plain();
        $this->definitionStyle = Style::plain();
    }

    public function withDefinitions(KeyedIterable<string,string> $definitions) : this
    {
        foreach($definitions as $term => $def) {
            $this->withDefinition($term, $def);
        }
        return $this;
    }

    public function withDefinition(string $term, string $definition) : this
    {
        $this->termWidth = max($this->termWidth, strlen($term));
        $this->definitionWidth = max($this->definitionWidth, strlen($definition));
        $this->definitions->set($term, $definition);
        return $this;
    }

    public function withTermStyle(StyleGroup $style) : this
    {
        $this->termStyle = $style;
        return $this;
    }

    public function withDefinitionStyle(StyleGroup $style) : this
    {
        $this->definitionStyle = $style;
        return $this;
    }

    public function __tostring() : string
    {
        return $this->render();
    }

    public function render() : string
    {
        $fetcher = $this->screenWidthFetcher;
        $screenWidth = $fetcher();

        // Make sure there is at least 2 character padding on the right
        $this->termWidth = min((int)floor($screenWidth - 2), $this->termWidth);

        // Leave at least 4 characters for padding
        $this->definitionWidth = min($screenWidth - 4, $this->definitionWidth);
        return implode(PHP_EOL . PHP_EOL, $this->definitions->mapWithKey(($term, $def) ==>
            Text::style($term)->toWidth($this->termWidth)->with($this->termStyle) . PHP_EOL .
            implode(PHP_EOL, Text::style($def)->toWidth($this->definitionWidth)->toVector()->map($line ==>
                Text::style('  ' . $line)->with($this->definitionStyle)
            ))
        ));
    }
}
