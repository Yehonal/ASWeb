<?php

namespace Hwc;

defined('HW_CORE_EXEC') or die('Restricted access');

/**
 * used for typehint
 */
class CoreAlias
{

    private $_alias;

    public function __construct($alias)
    {
        $this->_alias = "$alias"; // if already S_CoreAlias, then convert to string
    }

    public function __toString()
    {
        return $this->_alias;
    }

    public function getAlias()
    {
        return $this->_alias;
    }
}

class Core extends BaseClass
{

    private $_rootPath = null;

    private $_isRoot = false; // core in root mode

    public function __construct($rootPath = null)
    {
        if (self::isCli())
            $this->setRoot(true); //workaround
        

        $this->_rootPath = $rootPath == null ? getcwd() : $rootPath;
    }

    /**
     * Return the instance of Core, to create new instance, use newInstance
     * method!
     *
     * @param type $core_alias            
     * @param type $rootPath            
     * @return S_Core
     */
    public static function I($coreAlias = null, $rootPath = null, $getObject = true)
    {
        // get singleton
        return CoreInstantiator::getInstance(get_class(), $coreAlias, Array(
            $rootPath
        ), true, null, Array(), $getObject);
    }

    /**
     * method to create new instance of core and prepare it
     */
    public static function newInstance($coreAlias, $rootPath = null)
    {
        if (self::coreInstanceExists($coreAlias)) {
            trigger_error("Core Instance $coreAlias already exists! cannot create.", E_USER_ERROR); // exit
        }
        
        $core = Core::I($coreAlias, $rootPath);
        $core->prepareBase();
        return $core;
    }

    public function prepareBase()
    {
        Paths::I($this->getCoreAlias(), $this->_rootPath);
    }

    public function getRootPath()
    {
        return $this->_rootPath;
    }

    public function setRoot($bool)
    {
        $this->_isRoot = $bool;
    }

    /**
     *
     * @param type $system
     *            if true check system root instead internal
     * @return type
     */
    public function isRoot($system = false)
    {
        if ($system) {
            return 0 == posix_getuid();
        }
        
        return $this->_isRoot == true;
    }

    /**
     * ************************************
     *
     *
     * STATIC METHODS
     *
     *
     *
     * ************************************
     */
    public static function coreInstanceExists($alias)
    {
        // we can avoid args because core , for each alias, is singleton
        $res = Core::I($alias, null, false);
        return $res ? $res->isInstantiated() : false;
    }

    /**
     * used to check if core is running
     */
    public static function checkAccess()
    {
        if (! defined('HW_CORE_EXEC')) {
            trigger_error('Restricted access', E_USER_ERROR);
            die();
        }
    }
}
