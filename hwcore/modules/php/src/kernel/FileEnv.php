<?php

namespace Hwc;

defined('HW_CORE_EXEC') or die('Restricted access');

class fileEnv
{

    private $pharPath = null;

    function __construct()
    {
        $pharPath = \Phar::running(false);
        if ($pharPath!==null) {
            \Phar::interceptFileFuncs();
        }
    }
    
    public function isPharEnv() {
        return $pharPath!==null;
    }

    /**
     *
     * @param type $file            
     * @param type $filter
     *            can be "ondisk" or "wrapper"
     * @return type
     */
    public function checkFileEnv($file, $filter = null, $getPriority = null)
    {
        $wrapper = null;
        $ondisk = null;
        if (defined("HW2_PHAR_MODE")) {
            if (! strncmp($file, "phar://", strlen("phar://"))) {
                // if starts with phar:// then we must check for real
                $ondisk = str_replace(\Phar::running(), dirname(\Phar::running(false)), $file);
                $wrapper = $file;
            } else {
                $wrapper = str_replace(dirname($GLOBALS["pharPath"]), \Phar::running(), $file);
                
                $ondisk = $file;
            }
        } else {
            $ondisk = $file;
        }
        
        //echo("orig ".$file." <br> ondisk ".$ondisk." <br> wrapper ".$wrapper." <br>");
        $res = null;
        
        if ($ondisk && file_exists($ondisk)) {
            $res["ondisk"] = $ondisk;
        }
        
        if ($wrapper && file_exists($wrapper)) {
            $res["wrapper"] = $wrapper;
        }
        
        if (! empty($res)) {
            if ($filter)
                return $res[$filter];
            
            if ($getPriority)
                return ($getPriority == "ondisk" && array_key_exists("ondisk", $res)) ? $res["ondisk"] : $res["wrapper"];
        }
        
        return $res;
    }

    public function loadFile($file, $function = "require_once", $skipEnv = false)
    {
        if (! $skipEnv)
            $file = checkFileEnv($file, null, "ondisk");
        
        switch ($function) {
            case "require_once":
                require_once $file;
                break;
            case "require":
                require $file;
                break;
            case "include_once":
                include_once $file;
                break;
            case "include":
                include $file;
                break;
        }
    }
}