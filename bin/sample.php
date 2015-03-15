#! /usr/bin/env hhvm
<?hh // strict

require_once dirname(__DIR__) . '/vendor/autoload.php';

use kilahm\Clio\Clio;
use kilahm\Clio\Format\Style;
use kilahm\Clio\Format\Text;

function main() : void
{
    $clio = Clio::make();
    $testText = 'The quick brown fox jumps over the lazy dog';
    $mirror = new ReflectionClass(Style::class);
    foreach($mirror->getMethods(ReflectionMethod::IS_STATIC | ReflectionMethod::IS_PUBLIC) as $method) {
        if($method->name === 'make') {
            continue;
        }
        $clio->line(Text::style($testText)->with($method->invoke(null)));
    }
}

/* HH_FIXME[1002] */
main();
