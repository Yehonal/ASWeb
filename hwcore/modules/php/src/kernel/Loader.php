<?php

namespace Hwc;
// Check to ensure this file is within the rest of the framework
S_Core::checkAccess();

use Hw2\Paths as SP;

class S_incType extends S_ConstDefines
{

    const req = "require";

    const inc = "include";

    const req_once = "require_once";

    const inc_once = "include_once";
}

class S_LoadInfo
{

    public $type;

    public $autoLoad;

    public $incType = null;

    function __construct($type, $autoLoad = true, $incType = "")
    {
        $this->type = $type;
        $this->autoLoad = $autoLoad;
        $this->incType = $incType;
    }
}

class S_Loader
{
    /**
     * Static to avoid different paths for same key
     *
     * @var S_Paths
     */
    protected static $SP = null;
    
    public function __construct($coreAlias = null)
    {
        parent::__construct($coreAlias);
        // use paths from root instance
        if (! self::$SP)
            self::$SP = SP::I(S_CoreInstance::ROOT_ALIAS);
    }
    
    /**
     * get singleton instance
     *
     * @return S_LoaderClass
     */
    public static function I($coreAlias = null)
    {
        return S_CoreInstantiator::getInstance(get_called_class(), $coreAlias, func_get_args(), true);
    }

    /**
     * used to keep trace of already included to avoid multiple inclusions
     * static to have unique values in all instances
     *
     * @var Array
     */
    private static $_includedFiles = Array();

    /**
     *
     * @param type $coreAlias            
     * @return S_Loader
     */
    public static function I($coreAlias = null)
    {
        return parent::I($coreAlias);
    }

    public function loadPath($key)
    {
        $path = self::$SP->get($key);
        $info = $path->info;
        if (! $info || ! $info instanceof S_LoadInfo)
            return false;
        
        switch ($path->type) {
            case S_PathType::css:
                $inc = ! empty($info->incType) ? $info->incType : S_CssMgr::ref;
                S_CssMgr::inc($path, null, $inc);
                break;
            case S_PathType::js:
                $document = &S_Document::jDoc();
                $document->addScript($path->getUrl());
                break;
            case S_PathType::dir:
                if ($path->isUrl)
                    break;
                $this->scanPath($path);
                break;
            case S_PathType::php:
                if ($path->isUrl)
                    break;
                
                $incType = $info->incType;
                if (! empty($incType)) {
                    if (! $this->includePhpFile($path->path, $incType, true))
                        trigger_error("cannot " . $incType . " file " . $path->path, E_USER_ERROR);
                } else {
                    $this->scanPath($path);
                }
                break;
            default:
                ;
                break;
        }
    }

    public static function isIncluded($path)
    {
        return self::$_includedFiles[$path] === true;
    }

    /**
     *
     * @param type $name            
     * @param type $path            
     * @param type $destPath
     *            it's the path used to compile a php-css file
     *            that could be relative ( also empty string "" accepted) or
     *            absolute
     * @param type $autoLoad            
     * @param type $side            
     * @param type $verbose            
     */
    public function addCss($name, $path, $destPath = NULL, $incType = "", $autoLoad = true, $side = 2, $verbose = true)
    {
        $info = new S_LoadInfo(S_PathType::css, $autoLoad, $incType);
        
        if (! is_null($destPath)) {
            $src = self::$SP->build($name, $path, S_PathType::css, false, "", null, true, true);
            $dest = S_CssMgr::compile($src, $destPath);
            $path = $this->addInclude($name, $dest, $info, $side, false, $verbose);
        } else {
            $path = $this->addInclude($name, $path, $info, $side, false, $verbose);
        }
        
        return $path;
    }

    public function addUrl($name, $url, $type, $autoLoad = true, $side = 2, $verbose = true)
    {
        $info = new S_LoadInfo($type, $autoLoad);
        return $this->addInclude($name, $url, $info, $side, true, $verbose);
    }

    /**
     *
     * @param type $name            
     * @param type $path            
     * @param type $type            
     * @param type $autoLoad            
     * @param type $incType            
     * @param type $side            
     * @param type $verbose            
     * @return S_PathInfo
     */
    public function addPath($name, $path, $type, $autoLoad = true, $incType = "", $side = 2, $verbose = true)
    {
        $info = new S_LoadInfo($type, $autoLoad, $incType);
        return $this->addInclude($name, $path, $info, $side, false, $verbose);
    }
    
    /*
     *  getters and setters
     */
    public function getIncPath($name)
    {
        return self::$SP->get($name)->path;
    }

    /**
     *
     * @param int $type            
     * @return Array
     */
    public function getIncByType($type = 0)
    {
        $result = Array();
        $paths = self::$SP->getAllPaths();
        foreach ($paths as $key => $val) {
            if ($val->type == $type || $type == 0) {
                $result[$key] = $val;
            }
        }
        
        return $result;
    }

    /**
     *
     * @param type $name            
     * @param type $path            
     * @param S_LoadInfo $loadInfo
     *            contains info as autoload,type,namespace etc.
     * @param type $side            
     * @param type $isUrl            
     * @param type $verbose            
     * @return type path
     */
    private function addInclude($name, $path, S_LoadInfo $loadInfo, $side = 2, $isUrl = false, $verbose = true)
    {
        if ($path instanceof S_PathInfo) {
            $path->info = $loadInfo;
            $path = self::$SP->set($name, $path);
        } else {
            $path = $isUrl ? self::$SP->setUrl($name, $path, $loadInfo->type, "", $loadInfo, true, true, $verbose) : self::$SP->setPath($name, $path, $loadInfo->type, "", $loadInfo, true, true, $verbose);
        }
        
        if ($path === false && $verbose) {
            trigger_error("Error when setting path: " . $name, E_USER_ERROR);
            die();
        }
        
        if ($loadInfo->autoLoad) {
            if ($side == S_SiteSide::both || ($side == S_Core::isBackend())) {
                $this->loadPath($name);
            }
        }
        
        return $path;
    }

    private static function addIncluded($path)
    {
        self::$_includedFiles[$path] = true;
    }

    private function includePhpFile($path, $incType, $skipEnv = false)
    {
        if (! in_array($incType, S_incType::toArray()))
            return false;
        
        if (! self::isIncluded($path)) {
            self::addIncluded($path);
            loadFile($path, $incType, $skipEnv);
        }
        
        return true;
    }

    private function scanPath(S_PathInfo $path)
    {
        ! $path->wrapperPath or S_CIndex::I($this->getCoreAlias())->scanPath($path->wrapperPath);
        ! $path->path or S_CIndex::I($this->getCoreAlias())->scanPath($path->path);
    }
}