<?hh // strict

namespace kilahm\Clio\Args;

class Flag implements Opt
{
    use Arg;
    use Aliasable;

    protected int $count = 0;

    public function found() : void
    {
        $this->count++;
    }

    public function reset() : void
    {
        $this->count = 0;
    }

    public function occurances() : int
    {
        $this->trigger('parse');
        return $this->count;
    }
}
