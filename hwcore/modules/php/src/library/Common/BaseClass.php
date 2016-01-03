<?php

namespace Hwc;

Core::checkAccess();

abstract class BaseClass
{

    /**
     * legacy var, deprecated
     * 
     * @var type
     */
    private $_coreAlias = null;

    public function __construct($coreAlias = null)
    {
        $this->_coreAlias = $coreAlias;
    }

    public function __destruct()
    {
        S_CoreInstantiator::deleteInstance(S_Instantiator::getResourceId($this), false, true);
    }

    /**
     *
     * @return string Name of called class
     */
    public static function cname($removeNameSpace = false)
    {
        $class = get_called_class();
        
        if ($removeNameSpace) {
            $clParts = explode('\\', $class);
            // if class includes namespace
            if (count($clParts) > 1) {
                // get last element ( class )
                $class = $clParts[count($clParts) - 1];
            }
        }
        
        return $class;
    }

    public static function classPath()
    {
        $reflector = new \ReflectionClass(get_called_class());
        return $reflector->getFileName();
    }

    /**
     *
     * @return S_CoreInstance
     */
    public function getSelfInstance()
    {
        return S_CoreInstantiator::getInstanceById($this, false);
    }

    public function isRootInstance()
    {
        return $this->getSelfInstance()->isRootInstance();
    }

    public function getCoreAlias()
    {
        if (! $this->_coreAlias) {
            $this->_coreAlias = $this->getSelfInstance()->getCoreAlias();
        }
        
        return $this->_coreAlias;
    }
}