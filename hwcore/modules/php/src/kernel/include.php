<?php
namespace Hwc;

if (version_compare(PHP_VERSION, "5.3", "<")) {
    trigger_error("HWCore requires PHP version 5.3.0 or higher", E_USER_ERROR);
}

require_once "FileEnv.php";

$fEnv = new fileEnv();

$fEnv->loadFile(__DIR__ . DIRECTORY_SEPARATOR . "defines.php");
$fEnv->loadFile($libPath . "ClassManager" . DS . "base_class.php");
$fEnv->loadFile($libPath . "Utilities" . DS . "tools.php");
$fEnv->loadFile($libPath . "ObjectManager" . DS . "instantiator.php");
$fEnv->loadFile(__DIR__ . DS . "Core" . DS . "core.php");
$fEnv->loadFile(__DIR__ . DS . "Core" . DS . "core_instantiator.php");
$fEnv->loadFile(__DIR__ . DS . "Core" . DS . "instance.php");
$fEnv->loadFile($libPath . "Filesystem" . DS . "filemanager.php");
$fEnv->loadFile($libPath . "ClassManager" . DS . "class_index.php");
$fEnv->loadFile($libPath . "Filesystem" . DS . "loader.php");

