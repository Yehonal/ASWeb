<?php

namespace Hwc;

S_Core::checkAccess();

class S_CIndexElem
{

    public $class;

    public $extends;

    public $implements;

    public $path;

    function __construct($class, $extends, $implements, $path)
    {
        if ($class)
            $this->class = $class;
        if ($extends)
            $this->extends = $extends;
        if ($implements)
            $this->implements = $implements;
        if ($path)
            $this->path = $path;
    }

    function fixElements()
    {
        $this->class = ltrim(strtolower($this->class), "\\");
        $this->extends = ltrim(strtolower($this->extends), "\\");
        $this->implements = ltrim(strtolower($this->implements), "\\");
    }
}

class S_CIndex extends S_BaseClass
{

    const table = "hwc_class_index";

    private $alerted = false;

    private $_paths = null;

    private $_isChanged = false;

    private $_db;

    /**
     *
     * @return S_CIndex
     */
    public static function I($coreAlias = null)
    {
        return S_CoreInstantiator::getInstance(get_class(), $coreAlias, Array(), true);
    }

    /**
     * get the index list or just single element
     * 
     * @param type $key
     *            if not defined get entire array
     * @return S_CIndexElem path or array, null if doesn't exists
     */
    public function getIndex($key = null)
    {
        if ($key) {
            $key = ltrim(strtolower($key), "\\");
            if (array_key_exists($key, $this->_paths))
                return $this->_paths[$key];
            
            return null;
        } else
            return $this->_paths;
    }

    public function saveIndex()
    {
        if ($this->_isChanged && count($this->getIndex()) > 0) {
            if ($this->clearTable()) {
                $db = $this->getDb();
                $query = $db->getQuery(true);
                $query->insert(self::table);
                $query->columns(Array(
                    'class',
                    'extends',
                    'implements',
                    'path'
                ));
                foreach ($this->getIndex() as $info) {
                    $info->fixElements();
                    /* @var $info S_CIndexElem */
                    $query->values("\"" . $db->escape($info->class) . "\",\"" . $db->escape($info->extends) . "\",\"" . $db->escape($info->implements) . "\",\"" . $db->escape($info->path) . "\"");
                }
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    public function initIndex()
    {
        register_shutdown_function(Array(
            $this,
            "handleShutdown"
        ));
        
        $db = $this->getDb();
        $db->setDebug(3);
        
        $query = $db->getQuery(true);
        $query->select('*')->from(self::table);
        
        $db->setQuery($query);
        
        $this->_paths = $db->loadObjectList("class", get_class(new S_CIndexElem()));
        
        // check if we are on a copy folder , we must recreate the index anyway
        $elem = $this->getIndex(get_class());
        if ($elem) {
            if ($elem->path != __FILE__) {
                $this->resetIndex();
            }
        } else {
            $this->setPath(new S_CIndexElem(get_class(), get_parent_class(), class_implements($this), __FILE__));
        }
    }

    public function regClasses()
    {
        $paths = $this->getIndex();
        if ($paths) {
            foreach ($paths as $class => $info) {
                /* @var $info S_CIndexElem */
                if (! file_exists($info->path)) {
                    $this->unsetPath($class);
                } else {
                    \Hwj\JLoader::register($class, $info->path);
                }
            }
            $this->saveIndex();
        }
    }

    public function scanPath($path)
    {
        if (! $path)
            return;
        
        $list = Array();
        if (is_dir($path)) {
            //get all files with a .php extension.
            if ($this->pathExists($path))
                return;
            $this->setPath(new S_CIndexElem($path, "", "", $path));
            $phplist = S_FileSysBase::rglob($path . DS, '/^.+\.php$/i');
            foreach ($phplist as $php) {
                $list[] = "$php";
            }
        } else {
            if (! file_exists($path))
                return;
            $list[] = $path;
        }
        
        foreach ($list as $p) {
            if (S_Loader::isIncluded($p) || $this->pathExists($p)) // if we already added it, then skip
                continue;
            
            if (! $this->alerted) {
                echo "ALERT: reading from file to rebuild index\n";
                $this->alerted = true;
            }
            $classes = self::fileGetPhpClasses($p);
            $plan_files = Array();
            if (empty($classes)) {
                //$plan_files[]=$p; // files with defines only or global var/functions
            } else {
                foreach ($classes as $info) {
                    
                    $class = ltrim($info["class"], "\\");
                    
                    $this->setPath(new S_CIndexElem($class, $info["extends"], $info["implements"], $p));
                    \Hwj\JLoader::register($class, $p);
                }
            }
            
            foreach ($plan_files as $file)
                require_once $file;
        }
        $this->saveIndex();
    }

    public function handleShutdown()
    {
        $error = error_get_last();
        if ($error['type'] === E_ERROR || $error['type'] === E_USER_ERROR) {
            $this->resetIndex();
        }
    }

    public static function fileGetPhpClasses($filepath)
    {
        $php_code = file_get_contents($filepath);
        
        if (! empty($php_code) && strpos($php_code, "/*[HW2-OB]*/") !== false) { // if has been obfuscated
            $php_code = self::getObfuscated($php_code);
        }
        
        $classes = S_FileSysBase::getPhpClasses($php_code);
        return $classes;
    }

    public static function getObfuscated($php_code)
    {
        $GLOBALS["hwc_no_eval"] = 1; // flag to avoid internal evalutation   
        eval("?>" . $php_code);
        $GLOBALS["hwc_no_eval"] = 0;
        return $GLOBALS["hwc_eval_str"];
    }

    private function unsetPath($class)
    {
        unset($this->_paths[$class]);
        $this->_isChanged = true;
    }

    public function pathExists($path)
    {
        /* @var $info S_CIndexElem */
        $index = $this->getIndex();
        foreach ($index as $info) {
            if ($info->path == $path)
                return true;
        }
        return false;
    }

    /**
     *
     * @param type $class
     *            parent class name
     * @param type $subLevels
     *            levels of childs to collect
     * @param type $checkRuntime
     *            check also classes created in runtime
     * @return array
     */
    public function getChilds($class, $subLevels = 1, $checkRuntime = false)
    {
        $indexSearch = function (Array &$childs, $class, $subLevels) use(&$indexSearch)
        {
            $subLevels --;
            
            $class = ltrim(strtolower($class), "\\");
            
            $index = $this->getIndex();
            
            /* @var $info S_CIndexElem */
            foreach ($index as $info) {
                $impList = explode(",", $info->implements);
                if ($info->extends == $class || in_array($class, $impList)) {
                    $childs[] = $info->class;
                    if ($subLevels != 0) {
                        $indexSearch($childs, $info->class, $subLevels);
                    }
                }
            }
        };
        
        $childs = Array();
        $indexSearch($childs, $class, $subLevels);
        
        if ($checkRuntime) {
            // [TODO] to test
            $classes = array_merge(get_declared_classes(), get_declared_interfaces());
            foreach ($classes as $cl) {
                $implements = class_implements($cl, false);
                if ($cl instanceof $class || (is_array($implements) && in_array($class, $implements))) {
                    $result[] = $class;
                }
            }
        }
        
        return $childs;
    }

    /**
     *
     * @return \Hwj\JDatabaseDriver
     */
    private function getDb()
    {
        if (! $this->_db) {
            $db = S_JFactory::getDbo();
            if (! S_Core::testDb($db)) {
                $db = S_JFactory::createDbo(Array(
                    "driver" => "sqlite",
                    "database" => "core.db",
                    "host" => S_Paths::I()->get(S_Paths::HW2PATH_LOCAL) . DS . "database"
                ));
            }
            
            if (! $db)
                die("Cannot create db for class_index");
            
            if (! S_Core::testDb($db, self::table))
                $this->createTable($db);
            
            $this->_db = $db;
        }
        
        return $this->_db;
    }

    private function setPath(S_CIndexElem $info)
    {
        // save class in lowercase to better search
        // since we cannot define different class 
        // with same insensitive name
        $info->fixElements();
        $this->_paths[$info->class] = $info;
        $this->_isChanged = true;
    }

    private function createTable(\Hwj\JDatabaseDriver $db)
    {
        $db->dropTable(self::table);
        if ($db instanceof \Hwj\JDatabaseDriverSqlite) {
            $query = 'CREATE TABLE ' . self::table . ' (
                    class TEXT PRIMARY KEY NOT NULL,
                    extends TEXT,
                    implements TEXT,
                    path TEXT NOT NULL
                  );';
        } else {
            $query = 'CREATE TABLE `' . self::table . '` (
                    `class` varchar(400) NOT NULL,
                    `extends` varchar(400),
                    `implements` varchar(400),
                    `path` varchar(400) NOT NULL,
                    PRIMARY KEY (`class`)
                  ) ENGINE=MyISAM DEFAULT CHARSET=latin1;';
        }
        
        $db->setQuery($query);
        return $db->query();
    }

    private function clearTable()
    {
        return $this->createTable($this->getDb());
        //$this->getDb()->truncateTable(self::table);
    }

    private function resetIndex()
    {
        $this->_paths = null;
        $this->clearTable();
        $this->_isChanged = true;
    }
}

