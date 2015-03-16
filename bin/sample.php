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
    $clio = Clio::make();
    show_styles($clio);
    show_table($clio);
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
        Vector{'Row 2', 'Row 2', 'Row 2'},
        Vector{'Row 3', 'Row 3', 'Row 3'},
    };

    $clio->show($clio->table($data)->render());
}
