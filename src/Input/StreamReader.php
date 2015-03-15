<?hh // strict

namespace kilahm\Clio\Input;

use kilahm\Clio\Exception\ReadError;
use kilahm\Clio\Util\Stream as StreamUtil;

class StreamReader implements Reader
{
    public function __construct(protected resource $res)
    {
        $data = StreamUtil::inspect($res);
        if($data === null) {
            throw new ReadError('Resource passed to StreamReader is not a readable stream.');
        }
        stream_set_blocking($this->res, 0);
    }

    public function getLine() : string
    {
        $result = fgets($this->res);
        if($result === false) {
            throw new ReadError('Unable to read a line from the stream.');
        }
        return rtrim($result, PHP_EOL);
    }

    public function waitForLine() : string
    {
        $result = '';
        $r = [$this->res];
        $w = $e = null;
        while(mb_substr($result, -1) !== PHP_EOL) {
            $countChanged = stream_select($r, $w, $e, 5);
            if($countChanged === false) {
                throw new ReadError('Unable to wait for a line from the stream.');
            } elseif($countChanged > 0) {
                $result .= fgets($this->res);
            }
        }
        return rtrim($result, PHP_EOL);
    }

    public function getChar() : string
    {
        $result = fgetc($this->res);
        if($result === false) {
            throw new ReadError('Unable to read a character from the stream.');
        }
        return $result;
    }

    public function waitForChar() : string
    {
        $result = '';
        $r = [$this->res];
        $w = $e = null;
        while(strlen($result) === 0) {
            $countChanged = stream_select($r, $w, $e, 5);
            if($countChanged === false) {
                throw new ReadError('Unable to wait for a character from the stream.');
            } elseif($countChanged > 0) {
                $result .= fgetc($this->res);
            }
        }
        return $result;
    }

    public function getContents() : string
    {
        $result = stream_get_contents($this->res);
        if($result === false) {
            throw new ReadError('Unable to fetch the rest of the stream.');
        }
        return $result;
    }
}
