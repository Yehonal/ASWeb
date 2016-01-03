<?php

namespace Hwc;

class S_CoreInstance extends S_Instance
{

    const ROOT_ALIAS = "hwcore_root";

    /**
     *
     * @var S_CoreAlias
     */
    protected $_coreAlias;

    private $_adoptiveParent;

    function __construct($options)
    {
        $this->setCoreAlias($options["extra"]["keys"][S_CoreInstantiator::core_alias]);
        parent::__construct($options);
        
        new FileManager();
        
        $this->_adoptiveParent = $this->findAdoptiveParent();
        if ($this->_adoptiveParent)
            $this->_adoptiveParent->setChild($this);
    }

    public function getAdoptiveParent()
    {
        return $this->_adoptiveParent;
    }

    public function setAdoptiveParent(S_CoreInstance $parent)
    {
        $this->_adoptiveParent = $parent;
    }

    public function getCoreAlias()
    {
        return $this->_coreAlias;
    }

    public function setCoreAlias($alias)
    {
        if (is_string($alias))
            $this->_coreAlias = new S_CoreAlias($alias);
        elseif ($alias instanceof S_CoreAlias)
            $this->_coreAlias = $alias;
        else {
            //trigger_error ("Cannot create core alias, wrong type passed",E_USER_ERROR);
        }
    }

    public function isRootInstance()
    {
        return $this->_coreAlias->getAlias() == self::ROOT_ALIAS;
    }

    /**
     *
     * @return S_CoreInstance
     */
    public static function findParent($child)
    {
        return parent::findParent($child);
    }

    /**
     *
     * @return S_CoreInstance
     */
    protected function findAdoptiveParent()
    {
        $parent = self::findParent($this);
        
        if ($parent && (! $parent instanceof self || ! $parent->getCoreAlias() == $this->getCoreAlias())) {
            return S_Core::I($this->getCoreAlias(), null, false);
        }
        
        return null;
    }
}
