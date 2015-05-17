<?hh // strict

namespace kilahm\Clio\Args;

trait Aliasable
{
    require implements Opt;

    protected Set<string> $names = Set{};

    protected Vector<(function(string):void)> $akaListeners = Vector{};

    public function onAliasAddition((function(string):void) $listener) : this
    {
        $this->akaListeners->add($listener);
        return $this;
    }

    public function triggerAliasAddition(string $newAlias) : void
    {
        foreach($this->akaListeners as $l) {
            $l($newAlias);
        }
    }

    public function aka(string $name) : this
    {
        $this->names->add($name);
        $this->triggerAliasAddition($name);
        return $this;
    }

    public function names() : Set<string>
    {
        return $this->names->toSet();
    }
}
