<?hh // strict

namespace kilahm\Clio\Exception;

use kilahm\Clio\Args\Argument;

class ArgumentError extends \Exception
{
    public function __construct(string $message, public Argument $arg)
    {
        parent::__construct($message);
    }
}
