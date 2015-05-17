<?hh // strict

namespace kilahm\Clio\Args;

trait Arg
{
    protected string $description = '';
    protected string $name;
    protected Vector<(function(string):void)> $descriptionListeners = Vector{};
    protected Vector<(function():void)> $parseListeners = Vector{};

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function onParse((function():void) $listener) : this
    {
        $this->parseListeners->add($listener);
        return $this;
    }

    public function triggerParse() : void
    {
        foreach($this->parseListeners as $l) {
            $l();
        }
    }

    public function onDescriptionChange((function(string):void) $listener) : this
    {
        $this->descriptionListeners->add($listener);
        return $this;
    }

    public function triggerDescriptionChange(string $newDescription) : void
    {
        foreach($this->descriptionListeners as $l) {
            $l($newDescription);
        }
    }

    public function describedAs(string $description) : this
    {
        $this->description = $description;
        $this->triggerDescriptionChange($description);
        return $this;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
}
