<?php
namespace Hwc;

defined('HW_CORE_EXEC') or die('Restricted access');

defined("DS") or define('DS', DIRECTORY_SEPARATOR);

gc_enable();

isset($_SESSION) or session_start();

// PHP ini CONF
ini_set('display_errors', 1);
//error constants at:
//http://www.php.net/manual/en/errorfunc.constants.php
//error_reporting(E_ALL & ~(E_STRICT|E_NOTICE) );


ini_set('error_reporting', E_ALL & ~ (E_STRICT | E_NOTICE | E_WARNING));

ini_set('xdebug.remote_host', 'localhost');
ini_set('xdebug.remote_port', 9000);
ini_set('xdebug.remote_handler', 'dbgp');
ini_set('xdebug.max_nesting_level', 1000);

// enable assertion
assert_options(ASSERT_ACTIVE, 1);
assert_options(ASSERT_WARNING, 0);
assert_options(ASSERT_QUIET_EVAL, 1);

function my_assert_handler($file, $line, $code)
{
    echo "<hr>Assertion Failed:
        File '$file'<br />
        Line '$line'<br />
        Code '$code'<br /><hr />";
}

assert_options(ASSERT_CALLBACK, 'my_assert_handler');

// need to check if we are in shutdown process
register_shutdown_function(function ()
{
    $GLOBALS[S_CoreDef::HW2_SHUTDOWN_FLAG] = 1;
});

// version defines
define("HW_CORE_VERSION", "3.5.1");

abstract class ConstDefines
{

    public static function toArray()
    {
        $class = new \ReflectionClass(get_called_class());
        $consts = $class->getConstants();
        return $consts;
    }

    public static function getOrdinal($const)
    {
        return array_search($const, self::getConstNames());
    }

    public function __invoke($search)
    {
        if (is_int($search)) {
            $allKeys = self::getConstNames();
            return $allKeys[$search];
        } elseif (is_string($search)) {
            return self::getOrdinal($search);
        }
        
        return null;
    }

    public static function __callStatic($name, $arguments)
    {
        return self::getOrdinal($name);
    }

    private static function getConstNames()
    {
        $array = self::toArray();
        return array_keys($array);
    }
}

class S_CoreDef extends S_ConstDefines
{

    const NS = "Hw2"; //namespace

    const jNS = "Hwj";

    const LPrefix = "L_";

    const SPrefix = "S_";

    const HW2_SHUTDOWN_FLAG = "HW2_SHUTDOWN_FLAG";
}


