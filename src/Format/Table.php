<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\Util\Environment;

type TableSize = shape(
    'columnWidth' => int,
    'tableWidth' => int,
);

final class Table
{
    protected int $cols = 0;
    protected Vector<Vector<Text>> $data = Vector{};
    protected Vector<Vector<string>> $workingData = Vector{};
    protected int $maxColWidth = 30;

    public static function fromStrings(Traversable<Traversable<string>> $data) : this
    {
        $table = self::make(Vector{});
        foreach($data as $row) {
            $table->addStringRow($row);
        }
        return $table;
    }

    public static function make(Traversable<Traversable<Text>> $data) : this
    {
        return new self(
            $data,
            () ==> Environment::screenSize()['width'],
        );
    }

    /**
     * Inject a callback for the screen width to wait until the last moment to format the table
     */
    public function __construct(Traversable<Traversable<Text>> $data, protected (function():int) $screenWidthFetcher)
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

    public function addRow(Traversable<Text> $row) : this
    {
        $newRow = Vector::fromItems($row);
        $this->cols = max($this->cols, $newRow->count());
        $this->data->add($newRow);
        return $this;
    }

    public function addStringRow(Traversable<string> $row) : this
    {
        return $this->addRow(Vector::fromItems($row)->map($item ==> Text::style($item)));
    }

    public function __toString() : string
    {
        return $this->render();
    }

    public function render() : string
    {
        if($this->data->isEmpty()) {
            return '';
        }

        $size = $this->size();

        $workingData = $this->data->toVector();
        $head = $workingData->at(0);
        $workingData->removeKey(0);

        return
            $this->renderHead($head, $size) .
            implode(
                $this->makeBlankLine($size),
                $workingData->map($row ==> $this->renderRow($row, $size))
            ) .
            $this->makeLine($size) . PHP_EOL;
    }

    public function size() : TableSize
    {
        $fetcher = $this->screenWidthFetcher;
        // Make room for the borders
        $screenWidth = $fetcher() - $this->cols - 1;
        // Reduce column width if user selected
        $colwidth = min((int)floor($screenWidth / $this->cols), $this->maxColWidth);
        return shape(
            // Column width does not include borders
            'columnWidth' => $colwidth,
            // Add in the borders for the full table width
            'tableWidth' => $this->cols * ($colwidth + 1) + 1,
        );
    }

    public function renderHead(Vector<Text> $row, TableSize $size) : string
    {
        return
            $this->makeLine($size) . PHP_EOL .
            $this->renderRow($row, $size) .
            $this->makeLine($size) . PHP_EOL;
    }

    <<__Memoize>>
    private function makeLine(TableSize $size) : string
    {
        return '+' . str_repeat('-', $size['tableWidth'] - 2) . '+';
    }

    <<__Memoize>>
    private function makeBlankLine(TableSize $size) : string
    {
        $cellContent = str_repeat(' ', $size['columnWidth']);
        $cells = array_fill(0, $this->cols, $cellContent);
        return '|' . implode('|', $cells) . '|' . PHP_EOL;
    }

    public function renderRow(Vector<Text> $row, TableSize $size) : string
    {
        // If some cells have more line breaks than others
        $blankCell = str_repeat(' ', $size['columnWidth']);

        // Expand smaller rows
        for($i = $row->count(); $i < $this->cols; $i++) {
            $row->add(Text::style(''));
        }

        // Break cells into vectors of single lines of the appropriate width
        $linesByCell = $row->map($item ==> {
            $lines = $item->left()->toWidth($size['columnWidth'])->toVector();
            if($lines->count() > 1) {
                return $lines;
            }
            return $item->centered()->toVector();
        });

        // Find the maximum number of lines
        $lineCount = (int)array_reduce($linesByCell->toArray(), (int $previous, Vector<string>$cell) ==> max($previous, $cell->count()), 0);

        $formattedRow = Vector{};

        // Pick out each line for each cell, inserting a blank line if needed
        for($lineNum = 0; $lineNum < $lineCount; $lineNum++) {
            foreach($linesByCell as $col => $cell) {
                $line = $cell->get($lineNum);
                $row = $formattedRow->get($lineNum);
                if($row === null) {
                    $row = Vector{};
                    $formattedRow->add($row);
                }
                $row->add($line === null ? $blankCell : $line);
            }
        }

        // Implode the items with | and the rows with a newline
        return implode(PHP_EOL, $formattedRow->map($row ==> '|' . implode('|', $row) . '|')) . PHP_EOL;
    }

    public function renderFoot(TableSize $size) : string
    {
        return $this->makeLine($size);
    }
}
