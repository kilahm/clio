<?hh // strict

namespace kilahm\Clio\Test\Format;

use kilahm\Clio\BackgroundColor;
use kilahm\Clio\Format\Text;
use kilahm\Clio\Format\Style;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\UndoEffect;

class TextTest extends \HackPack\HackUnit\Core\TestCase
{
    private static string $content = 'plain text';

    public function testPlainTextIsPlain() : void
    {
        $this->expect(Text::style(self::$content)->with(Style::plain()))->toBeIdenticalTo(self::$content);
    }

    public function testForegroundColorOnly() : void
    {
        $style = Style::make(TextColor::blue);
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[34m" . self::$content . "\e[39m");
    }

    public function testBackgroundColorOnly() : void
    {
        $style = Style::make(null, BackgroundColor::blue);
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[44m" . self::$content . "\e[49m");
    }

    public function testForegroundAndBackGroundColor() : void
    {
        $style = Style::make(TextColor::blue, BackgroundColor::blue);
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[34;44m" . self::$content . "\e[39;49m");
    }

    public function testOneEffect() : void
    {
        $style = Style::make(null, null, Vector{TextEffect::italic});
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[3m" . self::$content . "\e[23m");
    }

    public function testManyEffects() : void
    {
        $effects = Vector{TextEffect::italic, TextEffect::bold, TextEffect::underline};
        $style = Style::make(null, null, $effects);
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[3;1;4m" . self::$content . "\e[23;22;24m");
    }

    public function testItAll() : void
    {
        $effects = Vector{TextEffect::italic, TextEffect::bold, TextEffect::underline};
        $style = Style::make(TextColor::blue, BackgroundColor::blue, $effects);
        $this->expect(Text::style(self::$content)->with($style))
            ->toEqual("\e[34;44;3;1;4m" . self::$content . "\e[39;49;23;22;24m");
    }
}
