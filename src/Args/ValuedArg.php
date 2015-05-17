<?hh // strict

namespace kilahm\Clio\Args;

trait ValuedArg
{
    use Arg;

    protected Vector<(function(string):bool)> $filters = Vector{};
    protected ?(function(string):string) $transform = null;
    protected ?string $default = null;
    protected Vector<(function(string):void)> $errorListeners = Vector{};

    public function onValidationError((function(string):void) $listener) : this
    {
        $this->errorListeners->add($listener);
        return $this;
    }

    protected function triggerValidationError(string $value) : void
    {
        foreach($this->errorListeners as $l) {
            $l($value);
        }
    }

    public function withDefault(string $default) : this
    {
        $this->default = $default;
        return $this;
    }

    public function valueIsOptional() : bool
    {
        return $this->default !== null;
    }

    public function filteredBy((function(string):bool) $filter) : this
    {
        $this->filters->add($filter);
        return $this;
    }

    public function transformedBy((function(string):string) $transform) : this
    {
        $this->transform = $transform;
        return $this;
    }

    public function withFilters(Traversable<(function(string):bool)> $filters) : this
    {
        $this->filters->addAll($filters);
        return $this;
    }

    private function check(string $value) : bool
    {
        foreach($this->filters as $filter) {
            if( ! $filter($value) ) {
                return false;
            }
        }
        return true;
    }
}
