<?php
namespace Hwc;

defined('HW_CORE_EXEC') or die('Restricted access');

class S_Instance
{

    private $object = null;

    private $objectInfo;

    private $signature = null;

    /**
     *
     * @var S_Instance
     */
    private $parent = null;

    private $childs = Array();

    /**
     *
     * @var type contains mixed extra informations
     *      about the object not used internally
     */
    private $extra;

    public function __construct($options)
    {
        if (! empty($options["signature"]))
            $this->signature = $options["signature"];
        
        if (! empty($options["objectInfo"]))
            $this->objectInfo = $options["objectInfo"];
        
        if (! empty($options["object"])) {
            $this->object = $options["object"];
        }
        
        if (! empty($options["extra"])) {
            $this->extra = $options["extra"];
        }
        
        $this->parent = self::findParent($this);
        if ($this->parent)
            $this->parent->setChild($this);
    }

    public function __destruct()
    {
        $this->destruct();
    }

    public function destruct($useGarbage = true)
    {
        if ($this->parent)
            $this->parent->unsetChild($this);
        
        $this->unsetVar($this->object, $useGarbage);
        $this->unsetVar($this->objectInfo, $useGarbage);
        $this->unsetVar($this->signature, $useGarbage);
        $this->unsetVar($this->parent, $useGarbage);
        $this->unsetVar($this->childs, $useGarbage);
        $this->unsetVar($this->extra, $useGarbage);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(S_Instance $parent)
    {
        $this->parent = $parent;
    }

    public function getChilds($id = null)
    {
        return $id ? $this->childs[$id] : $this->childs;
    }

    /**
     *
     * @param \Hw2\S_Instance $child            
     */
    public function setChild(S_Instance $child)
    {
        $this->childs[S_Instantiator::getResourceId($child)] = $child;
    }

    public function getExtra()
    {
        return $this->extra;
    }

    public function unsetChild(S_Instance $child)
    {
        unset($this->childs[S_Instantiator::getResourceId($child)]);
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function getObject($enableCreation = true, $removeInfo = true)
    {
        // instance is created only when really requested
        if (! is_object($this->object) && $enableCreation) {
            $this->object = self::createInstance($this->objectInfo["name"], $this->objectInfo["args"]);
            
            S_Instantiator::registerObject($this, $this->object, $this->signature);
            
            if (isset($this->objectInfo) && $removeInfo)
                unset($this->objectInfo); // unset array
        }
        
        return $this->object;
    }

    /**
     *
     * @return string
     */
    public function getObjectClass()
    {
        return $this->objectInfo["name"] ? $this->objectInfo["name"] : get_class($this->object);
    }

    public function isInstantiated()
    {
        return is_object($this->object);
    }

    /**
     *
     * @return S_Instance
     */
    public static function findParent($child)
    {
        $res = debug_backtrace();
        
        foreach ($res as $info) {
            if (! is_object($info["object"]))
                continue;
            
            if ($info["object"] !== $child && S_CoreInstantiator::isObjInList($info["object"])) {
                
                if ($info["object"] instanceof static) {
                    if ($info["object"]->getObject(false) !== $child) {
                        return $info["object"];
                    } else {
                        continue;
                    }
                } else {
                    return S_Instantiator::getInstanceById(S_Instantiator::getResourceId($info["object"]), false);
                }
            }
        }
    }

    private function unsetVar(&$var, $useGarbage)
    {
        if (isset($var)) {
            if ($useGarbage) {
                unset($var);
            } else {
                $var = NULL;
            }
        }
    }

    private static function createInstance($classname, Array $args)
    {
        try {
            if (! empty($args)) {
                $r = new \ReflectionClass($classname);
                return $r->newInstanceArgs($args);
            } else {
                return new $classname();
            }
        } catch (RuntimeException $e) {
            die('error: cannot create the instance');
            return null;
        }
    }
    
    /*
     * public function __call($name, $arguments) { return call_user_func_array(Array($this->getObject(),$name), $arguments); } public static function __callStatic($name, $arguments) { return call_user_func_array(Array($this->getObject(),$name), $arguments); }
     */
}

class S_Instantiator
{

    private static $_instances = Array();

    private static $_signatures = Array();

    /**
     * Get singleton instance for specified class name, if doesn't exist create it using passed args
     *
     * @param mixed $class
     *            either a string with the classname, or an object
     * @param array $args
     *            creator arguments, useless if object passed as $class parameter
     * @param Array $key
     *            can be a string, an array or anything can be serialized to specify an instance
     * @param bool $getObject            
     */
    public static function getSingleton($class, Array $args = Array(), Array $keys = Array(), $getObject = true)
    {
        return self::provideSigned($class, $args, Array(
            "singleton" => S_St,
            "keys" => $keys
        ), $getObject);
    }

    /**
     * Get instance for specified namespaced class and related args.
     * If not exists, first creates it
     *
     * @param string $name
     *            name of the class
     * @param array $args
     *            creator arguments
     * @param type $namespace            
     * @param Array $key
     *            can be a string, an array or anything can be serialized to specify an instance
     * @return type
     */
    public static function getNsSigned($name, Array $args = Array(), $namespace = S_CoreDef::NS, $keys = null, $getObject = true)
    {
        self::fixNS($namespace);
        return self::provideSigned($namespace . $name, $args, Array(
            "args" => $args,
            "keys" => $keys
        ), $getObject);
    }

    /**
     * Get instance for specified class and related args.
     * If not exists, first creates it
     *
     * @param mixed $class
     *            either a string with the classname, or an object
     * @param array $args
     *            creator arguments, useless if object passed as $class parameter
     * @param Array $key
     *            can be a string, an array or anything can be serialized to specify an instance
     * @return \stdClass
     */
    public static function getSigned($class, Array $args = Array(), $key = null, $getObject = true)
    {
        return self::provideSigned($class, $args, Array(
            "args" => $args,
            "keys" => $key
        ), $getObject);
    }

    /**
     *
     * @param mixed $class
     *            either a string with the classname, or an object
     * @param array $args
     *            creator arguments, useless if object passed as $class parameter
     * @param Array $options
     *            can be a string, an array or anything can be serialized to specify an instance
     * @param type $getObject
     *            return the created object, else return Instance class
     * @return type
     */
    private static function provideSigned($class, Array $args, Array $options, $getObject = true)
    {
        $name = is_object($class) ? get_class($class) : $class;
        
        $signature = self::createSignature($name, $options);
        
        if ($getObject && ! array_key_exists("$signature", self::$_signatures)) {
            
            // before create instance, try to find if it's a singleton
            if (! $options["singleton"] && null !== $instance = self::getSingleton($name, $args, $options["keys"], false)) {
                return $instance;
            }
            
            $opt["signature"] = $signature;
            if (is_object($class))
                $opt["object"] = $class;
            else
                $opt["objectInfo"] = Array(
                    "name" => $name,
                    "args" => $args
                );
            $opt["extra"] = $options;
            
            $instance = static::newInstance($opt);
            
            return self::getInstanceByObj($instance, $signature, $options, $getObject);
        }
        
        return self::getInstanceById($signature, $getObject);
    }

    /**
     * add an instance inside instantiator list and return it
     *
     * @param type $object            
     * @param type $options            
     * @return S_ Instance
     */
    public static function getInstanceByObj($object, $signature = null, $options = Array(), $getObject = true)
    {
        if (! is_object($object))
            return null;
        
        if (! self::isObjInList($object)) {
            if (! $object instanceof S_Instance) {
                $instanceObj = $object;
                $instance = self::newInstance(Array(
                    "object" => $instanceObj,
                    "extra" => $options,
                    "signature" => $signature
                ));
            } else
                $instance = $object; // rare case
            
            self::registerObject($instance, $instanceObj, $signature);
            
            $id = self::getResourceId($instance);
        } else
            $id = self::getResourceId($object);
        
        return self::getInstanceById($id, $getObject);
    }

    /**
     *
     * @param type $id
     *            can be the signature,instance/object id or array with classname and
     *            parameters -> keys ("name","args")
     * @return S_Instance
     */
    public static function getInstanceById($id, $getObject = true)
    {
        $obj = null;
        
        if ($id instanceof S_Instance) {
            $obj = $id;
        } elseif (is_array($id)) {
            $id = self::createSignature($id["name"], $id["args"]);
            $sign = self::$_signatures["$id"];
            $obj = self::$_instances["$sign"];
        } elseif ($id instanceof S\Signature) {
            $sign = self::$_signatures["$id"];
            $obj = self::$_instances["$sign"];
        } elseif ($id instanceof S_ResID || (is_string($id) && array_key_exists($id, self::$_instances))) {
            $obj = self::$_instances["$id"];
            if ($obj instanceof S_ResID)
                $obj = self::$_instances["$obj"];
        } elseif (is_object($id)) {
            return self::getInstanceById(self::getResourceId($id), $getObject);
        }
        
        if ($obj && $getObject) {
            return $obj->getObject();
        }
        
        return $obj;
    }

    public static function registerObject($instance, $object = null, $signature = null)
    {
        $id = self::getResourceId($instance);
        
        if ($signature)
            self::$_signatures["$signature"] = $id;
        if ($object) {
            $objID = self::getResourceId($object);
            self::$_instances["$objID"] = $id;
        }
        
        if (! self::isObjInList($instance))
            self::$_instances["$id"] = $instance;
    }

    /**
     *
     * @param type $id
     *            can be the signature or array with classname and
     *            parameters -> keys ("name","args")
     */
    public static function deleteInstance($id, $recursive = false, $useGarbage = true)
    {
        /* @var $obj S_Instance */
        $obj = self::getInstanceById($id, false);
        
        if ($obj) {
            $childs = $obj->getChilds();
            if ($recursive && ! empty($childs)) {
                foreach ($childs as $child) {
                    /* @var $child Hw2\S_Instance */
                    self::deleteInstance(self::getResourceId($child), $recursive, $useGarbage);
                }
            }
            
            // remove signature from list
            $signature = $obj->getSignature();
            if (array_key_exists("$signature", self::$_signatures)) {
                unset(self::$_signatures["$signature"]);
            }
            
            // remove instance object from list
            $objId = self::getResourceId($obj->getObject(false));
            if ($objId) {
                unset(self::$_instances["$objId"]);
            }
            
            // clean instance values
            $obj->destruct($useGarbage);
            
            // remove instance from list
            $resID = self::getResourceId($obj);
            if ($resID) {
                unset(self::$_instances["$resID"]);
            }
        } else {
            // special case
            if (is_object($id)) {
                unset(self::$_instances[self::getResourceId($id)->ID]);
            }
        }
    }

    public static function isObjInList($object)
    {
        return key_exists(self::getResourceId($object), self::$_instances);
    }

    /**
     *
     * @param type $object            
     * @return S_ResID
     */
    public static function getResourceId($object, $findInstance = false)
    {
        if ($findInstance) {
            $object = S_Instantiator::getInstanceById($object, false);
        }
        
        return $object ? new S_ResID(spl_object_hash($object)) : null;
    }

    /**
     * alias method to allow Instance class to be extended
     */
    protected static function newInstance($options)
    {
        return new S_Instance($options);
    }

    private static function createSignature($classname, Array $options = null)
    {
        $s = Array(
            "classname" => $classname
        );
        if (! empty($options))
            $s["options"] = $options;
        
        return S\Signature::createSignature($s, true);
    }
}

// singleton
final class S_St
{

    /**
     * Deprecated , use instantiator instead
     */
    public static function get($name, Array $args = Array(), $key = null)
    {
        return S_Instantiator::getSingleton($name, $args, $key);
    }
}

final class S_ResID
{

    public $ID;

    public function __construct($ID)
    {
        $this->ID = $ID;
    }

    public function __toString()
    {
        return "$this->ID";
    }
}

