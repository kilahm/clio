<?hh // strict

namespace kilahm\Clio\Util;

type ScreenSize = shape(
    'width' => int,
    'height' => int,
);

class Environment
{
    /**
     * Extract the command line arguments from the server superglobal
     */
    public static function argvFromServer() : Vector<string>
    {
        return Vector::fromItems(filter_input(INPUT_SERVER, 'argv', FILTER_UNSAFE_RAW));
    }

    /**
     * Helper method for ensuring we really are on the command line
     */
    public static function isCli() : bool
    {
        return substr(php_sapi_name(), 0, 3) === 'cli';
    }

    /**
     * Helper method for protecting this script from being served
     */
    public static function cliOrNotFound() : void
    {
        if( ! self::isCli()) {
            http_response_code(404);
            exit();
        }
    }

    /**
     * Get the current screen size
     */
    public static function screenSize() : ScreenSize
    {
        return shape(
            'width' => (int)exec('tput cols'),
            'height' => (int)exec('tput lines'),
        );
    }
}
