<?hh // strict

namespace kilahm\Clio\Args;

interface Opt
{
    public function reset() : void;
    public function occurances() : int;
    public function on(string $eventName, (function(...):void) $callback) : void;
    public function getName() : string;
    public function getDescription() : string;
    public function trigger(string $key, ...) : void;
}
