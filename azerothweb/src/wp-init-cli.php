<?php

namespace Azth;

//setup global $_SERVER variables to keep WP from trying to redirect
$_SERVER = array_merge($_SERVER, array(
    "HTTP_HOST" => "http://azerothshard.ga",
    "SERVER_NAME" => "http://azerothshard.ga",
    "REQUEST_URI" => "/",
    "REQUEST_METHOD" => "GET"
        ));


define('PATH_CURRENT_SITE', '/');
define('DOMAIN_CURRENT_SITE', 'azerothshard.ga');


define('BASE_PATH', AZTH_PATH_WP);

require_once(AZTH_PATH_WP . 'wp-load.php');

class WP_Command_Line {

    public function __construct() {
        //nothing here
    }

    public function main($args = array()) {
        $defaults = array(
            0 => 'run-test'
        );
        //$args = wp_parse_args($args, $defaults);

        switch ($args[0]) {
            case 'get-last-postdate':
                fwrite(STDOUT, get_lastpostdate() . "\n");
                break;
            case 'run-test':
                require_once AZTH_PATH_SRC . DS . 'tests' . DS . 'cli.php';
                break;
            default:
                echo ("Invalid command specified.\n");
                break;
        }
    }

}

$args = parseArgs($argv);
$importer = new WP_Command_Line();
$importer->main($args);

function parseArgs($argv) {
    array_shift($argv);
    $out = array();
    foreach ($argv as $arg) {
        if (substr($arg, 0, 2) == '--') {
            $eqPos = strpos($arg, '=');
            if ($eqPos === false) {
                $key = substr($arg, 2);
                $out[$key] = isset($out[$key]) ? $out[$key] : true;
            } else {
                $key = substr($arg, 2, $eqPos - 2);
                $out[$key] = substr($arg, $eqPos + 1);
            }
        } else if (substr($arg, 0, 1) == '-') {
            if (substr($arg, 2, 1) == '=') {
                $key = substr($arg, 1, 1);
                $out[$key] = substr($arg, 3);
            } else {
                $chars = str_split(substr($arg, 1));
                foreach ($chars as $char) {
                    $key = $char;
                    $out[$key] = isset($out[$key]) ? $out[$key] : true;
                }
            }
        } else {
            $out[] = $arg;
        }
    }
    return $out;
}
