<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\UndoEffect;

type StyleGroup = shape(
    'fg' => TextColor,
    'bg' => BackgroundColor,
    'effects' => Vector<TextEffect>,
);

<<__ConsistentConstruct>>
class Text
{
    private StyleGroup $style;

    public static function style(string $text) : this
    {
        return new static($text);
    }

    public function with(StyleGroup $style) : string
    {
        return $this
            ->setStyle($style)
            ->render();
    }

    public function setStyle(StyleGroup $style) : this
    {
        $this->style = $style;
        return $this;
    }

    public function __construct(private string $text = '')
    {
        $this->style = Style::make();
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

    public function __toString() : string
    {
        return $this->render();
    }

    public function render() : string
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
            return $this->text;
        }
        return sprintf("\e[%sm%s\e[%sm", implode(';', $onCodes), $this->text, implode(';', $offCodes));
    }
}
