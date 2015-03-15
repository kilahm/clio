<?hh // strict

namespace kilahm\Clio\Args;

class Argument
{
    use ValuedArg;

    protected string $value = '';

    public function set(string $value) : void
    {
        if($this->check($value)) {
            $t = $this->transform;
            if($t !== null) {
                $value = $t($value);
            }
            $this->value = $value;
        } else {
            $this->trigger('filter error', $value, $this);
        }
    }

    public function value() : string
    {
        $this->trigger('parse');
        if($this->value === '') {
            if($this->default !== null) {
                return $this->default;
            }
            $this->trigger('missing argument', $this);
        }
        return $this->value;
    }
}
