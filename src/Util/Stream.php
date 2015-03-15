<?hh // strict

namespace kilahm\Clio\Util;

type StreamData = shape(
    'timed_out' => bool,
    'blocking' => bool,
    'stream_type' => string,
    'wrapper_type' => string,
    'wrapper_data' => mixed,
    'mode' => string,
    'seekable' => bool,
    'uri' => string,
    'readable' => bool,
    'writable' => bool,
);

class Stream
{
    protected static Set<string> $readModes = Set{'r', 'r+', 'w+', 'a+', 'x+', 'c+'};
    protected static Set<string> $writeModes = Set{'r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'};

    public static function inspect(resource $res) : ?StreamData
    {
        if(get_resource_type($res) !== 'stream') {
            return null;
        }

        $data = stream_get_meta_data($res);
        return shape(
            'timed_out' => $data['timed_out'],
            'blocking' => $data['blocked'],
            'stream_type' => $data['stream_type'],
            'wrapper_type' => $data['wrapper_type'],
            'wrapper_data' => $data['wrapper_data'],
            'mode' => $data['mode'],
            'seekable' => $data['seekable'],
            'uri' => $data['uri'],
            'readable' => self::$readModes->contains($data['mode']),
            'writable' => self::$writeModes->contains($data['mode']),
        );
    }
}
