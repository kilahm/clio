<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\Util\Environment;

<<__ConsistentConstruct>>
class Table
{
    protected int $cols = 0;
    protected int $colWidth = 0;
    protected Vector<Vector<string>> $data = Vector{};
    protected int $maxColWidth = 30;

    public static function make(Traversable<Traversable<string>> $data) : this
    {
        return new static(
            $data,
            () ==> Environment::screenSize()['width'],
        );
    }

    public function __construct(Traversable<Traversable<string>> $data, protected (function():int) $screenWidthFetcher)
    {
        // This will also set the number of columns
        foreach($data as $row) {
            $this->addRow($row);
        }
    }

    public function withMaxColWidth(int $width) : this
    {
        $this->maxColWidth = $width;
        return $this;
    }

    public function addRow(Traversable<string> $row) : this
    {
        $newRow = Vector::fromItems($row);
        $this->cols = max($this->cols, $newRow->count());
        $this->data->add($newRow);
        return $this;
    }

    public function __toString() : string
    {
        return $this->render();
    }

    public function render() : string
    {
        $fetcher = $this->screenWidthFetcher;
        $this->colWidth = (int)floor($fetcher() / $this->cols);
        $this->colWidth = min($this->colWidth, $this->maxColWidth);

        $out = '';
        foreach($this->data as $i => $row) {
            if($i === 0) {
                $out .= $this->renderHead($row);
            } else {
                $out .= $this->renderRow($row);
            }
        }
        $out .= $this->renderFoot();
        return $out;
    }

    public function renderHead(Vector<string> $row) : string
    {
        return
            '+' . str_repeat('-', $this->cols * $this->colWidth - 1) . '+' . PHP_EOL .
            '|' . implode('|', $row->map($head ==> Text::style($head)->centered()->toWidth($this->colWidth - 1))) . '|' . PHP_EOL .
            '+' . str_repeat('-', $this->cols * $this->colWidth - 1) . '+' . PHP_EOL;
    }

    public function renderRow(Vector<string> $row) : string
    {
        return '|' . implode('|', $row->map($item ==> (string)Text::style($item)->centered()->toWidth($this->colWidth - 1))) . '|' . PHP_EOL;
    }

    public function renderFoot() : string
    {
        return '+' . str_repeat('-', $this->cols * $this->colWidth - 1) . '+' . PHP_EOL;
    }
}
