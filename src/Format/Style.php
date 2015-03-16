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

    /**
     * Remove all formatting control codes
     */
    public static function strip(string $in) : string
    {
        return $in;
    }

    public static function plain() : StyleGroup
    {
        return self::make();
    }

    public static function warn() : StyleGroup
    {
        return shape(
            'fg' => TextColor::black,
            'bg' => BackgroundColor::light_yellow,
            'effects' => Vector{TextEffect::bold},
        );
    }

    public static function info() : StyleGroup
    {
        return shape(
            'fg' => TextColor::white,
            'bg' => BackgroundColor::blue,
            'effects' => Vector{},
        );
    }

    public static function error() : StyleGroup
    {
        return shape(
            'fg' => TextColor::white,
            'bg' => BackgroundColor::red,
            'effects' => Vector{},
        );
    }

    public static function success() : StyleGroup
    {
        return shape(
            'fg' => TextColor::light_green,
            'bg' => BackgroundColor::normal,
            'effects' => Vector{TextEffect::bold},
        );
    }

    public static function tableHead() : StyleGroup
    {
        return shape(
            'fg' => TextColor::normal,
            'bg' => BackgroundColor::normal,
            'effects' => Vector{TextEffect::underline}
        );
    }
}
