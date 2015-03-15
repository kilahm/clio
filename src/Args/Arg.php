<?hh // strict

namespace kilahm\Clio\Args;

trait Arg
{
    use \HackPack\Hacktions\EventEmitter;

    protected string $description = '';
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function describedAs(string $description) : this
    {
        $this->description = '';
        $this->trigger('described', $description, $this);
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }
}
