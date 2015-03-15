<?hh // strict

namespace kilahm\Clio\Args;

trait Aliasable
{
    require implements Opt;

    protected Set<string> $names = Set{};

    public function aka(string $name) : this
    {
        $this->names->add($name);
        $this->trigger('aka', $name, $this);
        return $this;
    }

    public function names() : Set<string>
    {
        return $this->names->toSet();
    }
}
