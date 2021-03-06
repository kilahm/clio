<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\UndoEffect;

<<__ConsistentConstruct>>
class Text
{
    private StyleGroup $style;
    protected int $width = -1;
    private int $alignment = STR_PAD_RIGHT;

    public static function style(string $text) : this
    {
        return new static($text);
    }

    public function __construct(private string $text = '')
    {
        $this->width = mb_strlen($text);
        $this->style = Style::plain();
    }

    public function with(StyleGroup $style) : string
    {
        return $this
            ->setStyle($style)
            ->render();
    }

    public function toWidth(int $width) : this
    {
        $this->width = $width;
        return $this;
    }

    public function setStyle(StyleGroup $style) : this
    {
        $this->style = $style;
        return $this;
    }

    public function fg(TextColor $fg) : this
    {
        $this->style['fg'] = $fg;
        return $this;
    }

    public function bg(BackgroundColor $bg) : this
    {
        $this->style['bg'] = $bg;
        return $this;
    }

    public function addEffects(Traversable<TextEffect> $effects) : this
    {
        $this->style['effects']->addAll($effects);
        return $this;
    }

    public function addEffect(TextEffect $effect) : this
    {
        $this->style['effects']->add($effect);
        return $this;
    }

    public function centered() : this
    {
        $this->alignment = STR_PAD_BOTH;
        return $this;
    }

    public function left() : this
    {
        $this->alignment = STR_PAD_RIGHT;
        return $this;
    }

    public function right() : this
    {
        $this->alignment = STR_PAD_LEFT;
        return $this;
    }

    public function __toString() : string
    {
        return $this->render();
    }

    public function render() : string
    {
        return $this->applyStyleTo($this->aligned());
    }

    public function applyStyleTo(string $content) : string
    {
        $effectNames = TextEffect::getNames();
        $undoEffects = UndoEffect::getValues();

        $onCodes = Vector{};
        $offCodes = Vector{};
        if($this->style['fg'] !== TextColor::normal) {
            $onCodes->add($this->style['fg']);
            $offCodes->add(TextColor::normal);
        }
        if($this->style['bg'] !== BackgroundColor::normal) {
            $onCodes->add($this->style['bg']);
            $offCodes->add(BackgroundColor::normal);
        }
        foreach($this->style['effects'] as $effect) {
            $onCodes->add($effect);
            $offCodes->add($undoEffects[$effectNames[$effect]]);
        }
        if($onCodes->isEmpty()) {
            return $content;
        }
        return sprintf("\e[%sm%s\e[%sm", implode(';', $onCodes), $content, implode(';', $offCodes));
    }

    public function aligned() : string
    {
        if($this->width < 1) {
            return $this->text;
        }
        return implode(PHP_EOL, $this->breakToLines());
    }

    private function breakToLines() : Vector<string>
    {
        if($this->width < 1) {
            $text = $this->text;
        } else {
            $text = wordwrap($this->text, $this->width, PHP_EOL, true);
        }
        return Vector::fromItems(explode(PHP_EOL, $text))
            ->map($line ==> str_pad($line, $this->width, ' ', $this->alignment));
    }

    public function toVector() : Vector<string>
    {
        return $this->breakToLines()->map($line ==> $this->applyStyleTo($line));
    }

    public function raw() : string
    {
        return $this->text;
    }
}
