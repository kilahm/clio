<?hh // strict

namespace kilahm\Clio\Format;

use kilahm\Clio\Args\Arg;
use kilahm\Clio\Args\Argument;
use kilahm\Clio\Args\Flag;
use kilahm\Clio\Args\Option;
use kilahm\Clio\BackgroundColor;
use kilahm\Clio\TextColor;
use kilahm\Clio\TextEffect;

<<__ConsistentConstruct>>
class Help
{
    private static StyleGroup $titleStyle = shape(
        'fg' => TextColor::light_gray,
        'bg' => BackgroundColor::black,
        'effects' => Vector{TextEffect::bold},
    );

    private static StyleGroup $termStyle = shape(
        'fg' => TextColor::normal,
        'bg' => BackgroundColor::normal,
        'effects' => Vector{TextEffect::bold},
    );

    private static StyleGroup $descriptionStyle = shape(
        'fg' => TextColor::normal,
        'bg' => BackgroundColor::normal,
        'effects' => Vector{},
    );

    protected Vector<Argument> $args = Vector{};
    protected Vector<Flag> $flags = Vector{};
    protected Vector<Option> $options = Vector{};

    public function __construct(protected string $command = '')
    {
    }

    public function setCommandName(string $command) : void
    {
        $this->command = $command;
    }

    public function __toString() : string
    {
        return $this->render();
    }

    public function addArg(Argument $arg) : void
    {
        $this->args->add($arg);
    }

    public function addFlag(Flag $flag) : void
    {
        $this->flags->add($flag);
    }

    public function addOption(Option $option) : void
    {
        $this->options->add($option);
    }

    public function render() : string
    {
        $optTitle = Text::style(' Options ')->with(self::$titleStyle);

        $argText = $this->renderArgs();
        $optText = $this->renderOpts();

        if($argText !== '' && $optText !== '') {
            return $this->renderHead() . str_repeat(PHP_EOL, 2) .
                $argText . str_repeat(PHP_EOL, 3) .
                $optText . str_repeat(PHP_EOL, 2);
        } elseif($argText !== '') {
            return $this->renderHead() . str_repeat(PHP_EOL, 2) . $argText . str_repeat(PHP_EOL, 2);
        } elseif($optText !== '') {
            return $this->renderHead() . str_repeat(PHP_EOL, 2) . $optText . str_repeat(PHP_EOL, 2);
        } else {
            return $this->renderHead() . str_repeat(PHP_EOL, 2);
        }
    }

    private function renderArgs() : string
    {
        if($this->args->isEmpty()) {
            return '';
        } else {
            return Text::style(' Arguments ')->with(self::$titleStyle) . str_repeat(PHP_EOL, 2) .
                DefinitionList::make(Map::fromItems(
                    $this->args->map($arg ==> Pair{$arg->getName(), $arg->getDescription()})
                ))
                ->withTermStyle(self::$termStyle)
                ->render();
        }
    }

    private function renderOpts() : string
    {
        $allOpts = $this->options->map($opt ==> Pair{$this->formatNames($opt->getName(), $opt->names(), true), $opt->getDescription()})
            ->addAll($this->flags->map($flag ==> Pair{$this->formatNames($flag->getName(), $flag->names(), false), $flag->getDescription()}));
        if($allOpts->isEmpty()) {
            return '';
        } else {
            return Text::style(' Options ')->with(self::$titleStyle) . str_repeat(PHP_EOL, 2) .
                DefinitionList::make(Map::fromItems($allOpts))
                ->withTermStyle(self::$termStyle)
                ->withDefinitionStyle(self::$descriptionStyle)
                ->render();
        }
    }

    private function formatNames(string $name, Set<string> $aliases, bool $acceptsValue) : string
    {
        $dasher = (string $n) ==> {
            if(strlen($n) > 1) {
                return '--' . $n;
            }
            return '-' . $n;
        };
        if($aliases->isEmpty()) {
            return $dasher($name);
        }
        return $dasher($name) . ' / ' . implode(' / ', $aliases->map($dasher)) . ($acceptsValue ? ' <value>' : '');
    }

    private function renderHead() : string
    {
        $argnames = $this->args->map($a ==> $a->getName());
        $argnames->reverse();
        $argnames->add($this->command);
        $argnames->reverse();
        $commandline = implode(' ', $argnames);
        $cmdWidth = strlen($commandline) + 4;

        $title = Text::style('Usage:')->toWidth($cmdWidth)->bg(BackgroundColor::light_gray);
        $cmd = Text::style($commandline)
            ->centered()
            ->toWidth($cmdWidth)
            ->bg(BackgroundColor::light_gray);
        return $title->render() . PHP_EOL . $cmd->render();
    }
}
