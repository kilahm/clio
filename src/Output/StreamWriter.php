<?hh // strict

namespace kilahm\Clio\Output;

use kilahm\Clio\Exception\WriteError;
use kilahm\Clio\Util\Stream as StreamUtil;
use kilahm\Clio\Util\StreamData;

class StreamWriter implements Writer
{
    use WriterTrait;

    protected StreamData $meta;

    public function __construct(protected resource $res)
    {
        $data = StreamUtil::inspect($res);
        if($data === null || ! $data['writable']) {
            throw new WriteError('Resource given to StreamWriter is not a writable stream.');
        }
        $this->meta = $data;
    }

    public function write(string $content) : void
    {
        // If mb_* is alised as *, we need to use 8bit encoding to get byte length of string
        $usemb = ini_get('mbstring.func_overload');
        $content_length = $usemb ? mb_strlen($content , '8bit') : strlen($content);
        $sent = 0;

        // Make sure the entire string is sent
        while($sent < $content_length) {
            $next = $usemb ? mb_substr($content, $sent, null, '8bit') : substr($content, $sent);
            $result = fwrite($this->res, $next);

            // Stop sending when we error or can't send any more
            if($result === false || $result === 0) {
                break;
            }

            $sent += $result;
        }

        // Error if we couldn't send it all
        if($sent < $content_length) {
            throw new WriteError('Could only stream ' . $sent . ' of ' . $content_length . ' bytes of a string.');
        }
    }
}
