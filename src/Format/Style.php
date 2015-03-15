<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\UndoEffect;

class Style
{
    public static function make(
        ?TextColor $fg = null,
        ?BackgroundColor $bg = null,
        ?Traversable<TextEffect> $effects = null
    ) : StyleGroup
    {
        return shape(
            'fg' => $fg === null ? TextColor::normal : $fg,
            'bg' => $bg === null ? BackgroundColor::normal : $bg,
            'effects' => $effects === null ? Vector{} : Vector::fromItems($effects),
        );
    }

    public static function plain() : StyleGroup
    {
        return self::make();
    }

    public static function warn() : StyleGroup
    {
        return shape(
            'fg' => TextColor::yellow,
            'bg' => BackgroundColor::normal,
            'effects' => Vector{},
        );
    }
}
