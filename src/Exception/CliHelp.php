<?hh // strict

namespace kilahm\Clio\Exception;

class CliHelp extends \Exception
{
    public function __construct(string $reason)
    {
        parent::__construct($reason);
    }
}
