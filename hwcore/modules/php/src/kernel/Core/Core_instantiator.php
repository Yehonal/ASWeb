<?php

namespace Hwc;

/**
 * Abstract to avoid initialization
 */
abstract class S_CoreInstantiator extends S_Instantiator
{

    const core_alias = "core_alias";

    protected static function &newInstance($options)
    {
        return new S_CoreInstance($options);
    }

    /**
     *
     * @param string $className            
     * @param string $coreAlias
     *            specify the core alias for this istance, if not specified
     *            try to get automatically when called inside core or use
     *            ROOT_ALIAS if not found.
     * @param Array $args
     *            pass the array of arguments, you can use func_get_args in
     *            extended class
     * @return type
     */
    public static function getInstance($className, $coreAlias = null, Array $args = Array(), $singleton = false, $namespace = null, Array $extraKeys = Array(), $getObject = true)
    {
        $key = self::findAlias($coreAlias, $className);
        
        $keys = Array(
            self::core_alias => $key
        );
        
        if (! empty($extraKeys))
            $keys = array_merge($keys, $extraKeys);
        
        S_FileSysBase::fixNS($namespace);
        
        if ($singleton)
            $instance = self::getSingleton($namespace . $className, $args, $keys, $getObject);
        else 
            if ($namespace)
                $instance = self::getNsSigned($className, $args, $namespace, $keys, $getObject);
            else
                $instance = self::getSigned($namespace . $className, $args, $keys, $getObject);
        
        return $instance;
    }

    public static function getInstanceByObj($object, $coreAlias = null, Array $extraKeys = Array(), $getObject = true)
    {
        return self::getInstance($object, $coreAlias, Array(), null, $extraKeys, $getObject);
    }

    /**
     * Alternative short getInstance using arg list instead array
     *
     * @param string $className            
     * @param type $coreAlias            
     * @param mixed $arg1            
     * @param mixed $_            
     * @return type
     */
    public static function I()
    {
        $args = func_get_args();
        return self::getInstance($args[0], $args[1], array_slice($args, 1));
    }

    /**
     *
     * @param type $coreAlias            
     * @return S_CoreAlias
     */
    public static function findAlias($coreAlias, $className = null)
    {
        if ($coreAlias !== null) {
            // To Avoid coreInstanceExists->findalias loop, 
            // if classname is S_Core we assume that coreAlias must be valid
            // otherwise no instance will be found
            if ($className == S_Core::cname() || S_Core::coreInstanceExists($coreAlias) || $coreAlias == S_CoreInstance::ROOT_ALIAS)
                $key = $coreAlias;
            else
                return null;
        } else {
            $parent = S_CoreInstance::findParent();
            if ($parent && $parent instanceof S_CoreInstance) {
                $key = $parent->getCoreAlias(); // it returns core alias if called inside core instance
            }
            // if we don't pass alias externally to core instance
            // we use ROOT_ALIAS as default value
            if ($key == null)
                $key = S_CoreInstance::ROOT_ALIAS;
        }
        
        if (is_string($key))
            $key = new S_CoreAlias($key);
        
        return $key;
    }
}