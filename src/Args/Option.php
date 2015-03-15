<?hh // strict

namespace kilahm\Clio\Args;

class Option implements Opt
{
    use ValuedArg;
    use Aliasable;

    protected Vector<string> $values = Vector{};
    protected bool $required = false;

    public function set(?string $value) : void
    {
        if($value === null || $value === '') {
            $value = '';
            if($this->required) {
                $this->trigger('missing option value', $this);
            }
        }

        if($this->check($value)) {
            $t = $this->transform;
            if($t !== null) {
                $value = $t($value);
            }
            $this->values->add($value);
        } else {
            $this->trigger('filter error', $value, $this);
        }
    }

    public function firstValue() : string
    {
        return $this->pickedValue($this->allValues()->get(0));
    }

    public function lastValue() : string
    {
        $all = $this->allValues();
        return $this->pickedValue($all->get($all->count() - 1));
    }

    public function pickedValue(?string $val) : string
    {
        if($val === null) {
            $val = $this->default;
        }
        return $val === null ? '' : $val;
    }

    public function allValues() : Vector<string>
    {
        $this->trigger('parse');
        return $this->values->toVector();
    }

    public function reset() : void
    {
        $this->values->clear();
    }

    public function occurances() : int
    {
        $this->trigger('parse');
        return $this->values->count();
    }

    public function withRequiredValue() : this
    {
        $this->required = true;
        return $this;
    }
}
