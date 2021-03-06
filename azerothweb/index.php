<?php

namespace Azth;

define("AZTH_WEB_RUN", true);
define("AZTH_IS_CLI", php_sapi_name() === 'cli');
define("AZTH_NS", __NAMESPACE__ . '\\');

defined("DS") or define('DS', DIRECTORY_SEPARATOR);

define("AZTH_PATH_ROOT_CUR", dirname(__FILE__));
// if hwcore is symlinked , it's the original path
define("AZTH_PATH_ROOT", realpath(AZTH_PATH_ROOT_CUR));
define("AZTH_PATH_GLOBAL", realpath(AZTH_PATH_ROOT . DS . ".." . DS));
define("AZTH_PATH_SRC", AZTH_PATH_ROOT . DS . "src");
define("AZTH_PATH_BIN", AZTH_PATH_ROOT . DS . "bin");
define("AZTH_PATH_MODULES", AZTH_PATH_ROOT . DS . "modules");

// if phar archive exists then load it instead of sources
$phar = AZTH_PATH_BIN . DS . "azerothweb.phar";
require_once ( file_exists($phar) ? $phar : AZTH_PATH_SRC . DS . "boot.php");
