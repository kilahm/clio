<?hh // strict

namespace kilahm\Clio\Args;

final class Argument
{
    use ValuedArg;

    protected Vector<(function():void)> $missingListeners = Vector{};
    protected bool $optional = false;
    protected string $value = '';

    public function onMissing((function():void) $listener) : this
    {
        $this->missingListeners->add($listener);
        return $this;
    }

    protected function triggerMissing() : void
    {
        foreach($this->missingListeners as $l) {
            $l();
        }
    }

    public function set(string $value) : void
    {
        if($this->check($value)) {
            $t = $this->transform;
            if($t !== null) {
                $value = $t($value);
            }
            $this->value = $value;
        } else {
            $this->triggerValidationError($value);
        }
    }

    public function value() : string
    {
        $this->triggerParse();
        if($this->value === '') {
            if($this->default !== null) {
                return $this->default;
            }
            $this->triggerMissing();
        }
        return $this->value;
    }
}
