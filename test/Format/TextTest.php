<?hh // strict

namespace kilahm\Clio\Test\Format;

use HackPack\HackUnit\Contract\Assert;
use kilahm\Clio\BackgroundColor;
use kilahm\Clio\Format\Text;
use kilahm\Clio\Format\Style;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;
use kilahm\Clio\UndoEffect;

<<TestSuite>>
class TextTest
{
    private static string $content = 'The quick brown fox jumps over the lazy dog';

    <<Test>>
    public function testPlainTextIsPlain(Assert $assert) : void
    {
        $assert->string(Text::style(self::$content)->with(Style::plain()))
            ->is(self::$content);
    }

    <<Test>>
    public function testForegroundColorOnly(Assert $assert) : void
    {
        $style = Style::make(TextColor::blue);
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[34m" . self::$content . "\e[39m");
    }

    <<Test>>
    public function testBackgroundColorOnly(Assert $assert) : void
    {
        $style = Style::make(null, BackgroundColor::blue);
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[44m" . self::$content . "\e[49m");
    }

    <<Test>>
    public function testForegroundAndBackGroundColor(Assert $assert) : void
    {
        $style = Style::make(TextColor::blue, BackgroundColor::blue);
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[34;44m" . self::$content . "\e[39;49m");
    }

    <<Test>>
    public function testOneEffect(Assert $assert) : void
    {
        $style = Style::make(null, null, Vector{TextEffect::italic});
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[3m" . self::$content . "\e[23m");
    }

    <<Test>>
    public function testManyEffects(Assert $assert) : void
    {
        $effects = Vector{TextEffect::italic, TextEffect::bold, TextEffect::underline};
        $style = Style::make(null, null, $effects);
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[3;1;4m" . self::$content . "\e[23;22;24m");
    }

    <<Test>>
    public function testItAll(Assert $assert) : void
    {
        $effects = Vector{TextEffect::italic, TextEffect::bold, TextEffect::underline};
        $style = Style::make(TextColor::blue, BackgroundColor::blue, $effects);
        $assert->string(Text::style(self::$content)->with($style))
            ->is("\e[34;44;3;1;4m" . self::$content . "\e[39;49;23;22;24m");
    }

    <<Test>>
    public function testTextBigWidth(Assert $assert) : void
    {
        $assert->string((string)Text::style(self::$content)->toWidth(50))
            ->is(str_pad(self::$content, 50));
    }

    <<Test>>
    public function testTextWidth15(Assert $assert) : void
    {
        $expected =
            'The quick brown' . PHP_EOL .
            'fox jumps over ' . PHP_EOL .
            'the lazy dog   ';
        $assert->string((string)Text::style(self::$content)->toWidth(15))
            ->is($expected);
    }

    <<Test>>
    public function testTextWidth13(Assert $assert) : void
    {
        $expected =
            'The quick    ' . PHP_EOL .
            'brown fox    ' . PHP_EOL .
            'jumps over   ' . PHP_EOL .
            'the lazy dog ';

        $assert->string((string)Text::style(self::$content)->toWidth(13))
            ->is($expected);
    }

    <<Test>>
    public function testTextWidth15WithColor(Assert $assert) : void
    {
        $expected =
            "\e[34;44m" .
            'The quick brown' . PHP_EOL .
            'fox jumps over ' . PHP_EOL .
            'the lazy dog   ' .
            "\e[39;49m";
        $style = Style::make(TextColor::blue, BackgroundColor::blue);
        $assert->string(Text::style(self::$content)->toWidth(15)->with($style))
            ->is($expected);
    }

    <<Test>>
    public function testTextToVector13(Assert $assert) : void
    {
        $expected = Vector{
            'The quick    ',
            'brown fox    ',
            'jumps over   ',
            'the lazy dog ',
        };
        $assert->mixed(Text::style(self::$content)->toWidth(13)->toVector())
            ->looselyEquals($expected);
    }

    <<Test>>
    public function testColoredTextToVector13(Assert $assert) : void
    {
        $expected = Vector{
            "\e[34m" . 'The quick    ' . "\e[39m",
            "\e[34m" . 'brown fox    ' . "\e[39m",
            "\e[34m" . 'jumps over   ' . "\e[39m",
            "\e[34m" . 'the lazy dog ' . "\e[39m",
        };
        $assert->mixed(Text::style(self::$content)->fg(TextColor::blue)->toWidth(13)->toVector())
            ->looselyEquals($expected);
    }
}
