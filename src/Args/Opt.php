<?hh // strict

namespace kilahm\Clio\Args;

interface Opt
{
    public function reset() : void;
    public function occurances() : int;
    public function getName() : string;
    public function getDescription() : string;
    public function onAliasAddition((function(string):void) $listener) : this;
    public function onParse((function():void) $listener) : this;
}
