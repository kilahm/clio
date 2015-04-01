#! /usr/bin/env hhvm
<?hh // strict

require_once dirname(__DIR__) . '/vendor/autoload.php';

use kilahm\Clio\Clio;
use kilahm\Clio\Format\Style;
use kilahm\Clio\Format\Text;

/* HH_FIXME[1002] */
main();

function main() : void
{
    $clio = Clio::fromCli();
    show_styles($clio);
    show_table($clio);
    show_help($clio);
}

function show_styles(Clio $clio) : void
{
    $testText = 'The quick brown fox jumps over the lazy dog';
    $mirror = new ReflectionClass(Style::class);
    $clio->line('');

    $methodsToIgnore = Set{
        'make',
        'strip',
    };
    foreach($mirror->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
        if($methodsToIgnore->contains($method->name)) {
            continue;
        }
        $clio->line($method->name . ':');
        $clio->line(' ' . Text::style($testText)->with($method->invoke(null)));
        $clio->line('');
    }
}

function show_table(Clio $clio) : void
{
    $data = Vector{
        Vector{'Head 1', 'Head 2', 'Head 3'},
        Vector{'Row 1', 'Row 1', 'Row 1'},
        Vector{'Row 2', 'Random really long text that should go to the next line.  This shows you how the table adapts to its content.', 'Row 2'},
        Vector{'Row 3', 'Row 3', 'Row 3'},
    };

    $clio->show($clio->table($data)->render());
    $clio->show($clio->table($data)->withMaxColWidth(4)->render());
}

function show_help(Clio $clio) : void
{
    $clio->option('long-name')->describedAs('An option with a long name!');
    $clio->option('a')->describedAs('An option with a short name.');
    $clio->option('e')->aka('even-longer')->describedAs('A short option with an alias.');
    $clio->option('looooong')->aka('l')->describedAs('A long option with a short alias.');

    $clio->arg('arg1')->describedAs('The first argument.');
    $clio->arg('another-argument')->describedAs('Another argument.');
    $clio->showHelp('This is the reason string for the help.');
}
