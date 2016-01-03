<?php

namespace Hwc;

class Signature
{

    private $signature = null;

    /**
     * Not sorted/modified options passed to build signature
     * 
     * @var type
     */
    private $originOpt = null;

    public function __construct($options, $sort = true)
    {
        $this->originOpt = $options;
        $this->signature = self::_createSig($options, $sort);
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function getOptions()
    {
        return $this->originOpt;
    }

    public function __toString()
    {
        return $this->signature;
    }

    public static function createSignature($options, $createInstance = false, $sort = true)
    {
        if ($options instanceof self)
            return $options;
        
        return $createInstance ? new self($options, $sort) : self::_createSig($options, $sort);
    }

    private static function _createSig($options, $sort = true)
    {
        if ($sort)
            MyArray::deepKsort($options);
        
        return md5(serialize($options));
    }
}

