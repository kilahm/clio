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
    private static string $content = 'The quick brown fox jumps over the lazy dog';

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

    public function testTextBigWidth() : void
    {
        $this->expect((string)Text::style(self::$content)->toWidth(50))
            ->toBeIdenticalTo(str_pad(self::$content, 50));
    }

    public function testTextWidth15() : void
    {
        $expected =
            'The quick brown' . PHP_EOL .
            'fox jumps over ' . PHP_EOL .
            'the lazy dog   ';
        $this->expect((string)Text::style(self::$content)->toWidth(15))
            ->toBeIdenticalTo($expected);
    }

    public function testTextWidth13() : void
    {
        $expected =
            'The quick    ' . PHP_EOL .
            'brown fox    ' . PHP_EOL .
            'jumps over   ' . PHP_EOL .
            'the lazy dog ';

        $this->expect((string)Text::style(self::$content)->toWidth(13))
            ->toBeIdenticalTo($expected);
    }

    public function testTextWidth15WithColor() : void
    {
        $expected =
            "\e[34;44m" .
            'The quick brown' . PHP_EOL .
            'fox jumps over ' . PHP_EOL .
            'the lazy dog   ' .
            "\e[39;49m";
        $style = Style::make(TextColor::blue, BackgroundColor::blue);
        $this->expect(Text::style(self::$content)->toWidth(15)->with($style))
            ->toBeIdenticalTo($expected);
    }

    public function testTextToVector13() : void
    {
        $expected = Vector{
            'The quick    ',
            'brown fox    ',
            'jumps over   ',
            'the lazy dog ',
        };
        $this->expect(Text::style(self::$content)->toWidth(13)->toVector())
            ->toEqual($expected);
    }

    public function testColoredTextToVector13() : void
    {
        $expected = Vector{
            "\e[34m" . 'The quick    ' . "\e[39m",
            "\e[34m" . 'brown fox    ' . "\e[39m",
            "\e[34m" . 'jumps over   ' . "\e[39m",
            "\e[34m" . 'the lazy dog ' . "\e[39m",
        };
        $this->expect(Text::style(self::$content)->fg(TextColor::blue)->toWidth(13)->toVector())
            ->toEqual($expected);
    }
}
