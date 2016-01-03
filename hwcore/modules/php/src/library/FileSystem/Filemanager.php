<?php
namespace Hwc;

Core::checkAccess();

class PathKey extends Signature
{

    public static function createSignature($options)
    {
        return parent::createSignature($options, true);
    }
}

final class PathType
{
    // the int value is only an index
    /**
     * default can be used to auto-check path type
     */
    const def = 0;

    const css = 1;

    const php = 2;

    const js = 3;

    const dir = 4; // directory
    /* const url_dir=8; */
}

class PathInfo
{

    public $key;

    private $path; // unresolved path

    
    /**
     *
     * @var mixed ,
     */
    public $parts;

    public $type;

    public $isUrl;

    public $global_var;

    public $info; // used for any kind of data to store with path

    
    // in case of symbolic link or relative paths:
    public $target; // resolved path

    public $wrapperPath;

    public $useRealPath;

    public function __construct($key, $path, Array $parts, $type = 0, $global_var = "", $isUrl = false, $info = null, $target = null, $wrapperPath = null, $useRealPath = true)
    {
        $this->key = PathKey::createSignature($key);
        $this->path = FileSysBase::normalizePath($path, DS, true); // unresolved path
        $this->parts = $parts;
        $this->type = $type; // type of file/path
        $this->isUrl = $isUrl; // is it an url or localfile
        $this->global_var = $global_var;
        $this->info = $info;
        $this->target = $target;
        $this->wrapperPath = FileSysBase::normalizePath($wrapperPath, DS, true);
        $this->useRealPath = $useRealPath;
    }

    public function __toString()
    {
        return $this->get();
    }

    public function __get($name)
    {
        switch ($name) {
            case "path":
                return $this->get();
                break;
        }
    }

    /**
     *
     * @param type $short
     *            get relative paths ( Path ) or path only without host part (
     *            URL )
     * @return string
     */
    public function get($short = false)
    {
        return $this->isUrl ? $this->getUrl($short) : $this->getPath($short);
    }

    /**
     *
     * @param type $pathonly            
     * @return string
     */
    public function getUrl($pathonly = false)
    {
        if ($this->isUrl) {
            //if the path is already defined as an url just return it
            $result = $this->path;
        } else {
            $path = Paths::I();
            // else create from filepath.
            // first check if it's not symlinked path and replace
            // else check for symlink original path
            // then replace all directory separators with correct one ( needed if we are on windows)
            

            $orig = $path->get(Paths::HW2PATH_CORE_ORIGIN)->getPath();
            $searchPath = substr($this->path, 0, strlen($orig)) == $orig ? $orig : $path->get(Paths::HW2PATH_ROOT)->getPath();
            $search = Array(
                $searchPath,
                DS
            );
            $replace = Array(
                $path->get(Paths::HW2PATH_ROOT_URL)->getUrl(),
                "/"
            );
            $result = str_replace($search, $replace, $this->getUnresolvedPath());
        }
        
        if ($pathonly) {
            $result = str_replace(Uri::getUriPrefix(), "", $result);
        }
        
        return $result;
    }

    /**
     *
     * @param type $relative            
     * @param type $real            
     * @param type $wrapperPrecedence            
     * @return string
     */
    public function getPath($relative = false, $real = true, $wrapperPrecedence = false)
    {
        // if it isn't url, then just get the path
        // else replace the url part
        if (! $this->isUrl) {
            $target = $this->getTargetPath();
            $result = ($this->useRealPath && $target && $real) ? $target : $this->getUnresolvedPath();
            
            // if wrapperPath has inode 0 and isn't a directory, then it's a mounted file.
            // So we force to use the mounted path instead ondisk to avoid inclusion conflicts.
            // or not result or wrapperPrecedence
            if (defined("HW2_PHAR_MODE") && $this->wrapperPath && ((! is_dir($this->wrapperPath) && fileinode($this->wrapperPath) === 0) || (! $result || $wrapperPrecedence))) {
                return $this->wrapperPath;
            }
            
            if ($relative) {
                $result = FileSysBase::findRelativePath(Paths::I()->get(Paths::HW2PATH_ROOT), $result);
            }
        } else {
            // remove url base path
            $result = str_replace(
                // search
                Array(
                    Paths::I()->get(Paths::HW2PATH_ROOT_URL)->getUrl(),
                    "/"
                ), 
                // replace
                Array(
                    "." . DS,
                    DS
                ), 
                // string
                $this->path);
            // then get the absolute position of ./path
            if (! $relative)
                $result = realpath($result);
        }
        
        return $result;
    }

    /**
     * Original path.
     * Symlink not resolved
     *
     * @return type
     */
    public function getUnresolvedPath()
    {
        return $this->path;
    }

    /**
     * real path, resolved symlink path if exists
     *
     * @return type
     */
    public function getTargetPath()
    {
        return $this->target;
    }

    public function isLocal()
    {
        foreach ($this->parts as $part) {
            if (self::partToPath($part, $this->isUrl) == $this->get(Paths::HW2PATH_LOCAL));
        }
    }

    public static function partToPath($part, $isUrl, $rebuild = false)
    {
        if ($part instanceof PathKey) {
            if ($part->isUrl != $isUrl) {
                trigger_error("Part " . $part->getOptions() . " has not url flag =" . $isUrl, E_USER_ERROR);
                die();
            }
            
            return Paths::I()->get($part, $rebuild);
        } else 
            if (is_string($part)) {
                return $part;
            } else {
                trigger_error("invalid path type of " . $part, E_USER_ERROR);
                die();
            }
    }
}

final class Paths extends BaseClass
{

    const HW2PATH_ROOT = "HW2PATH_ROOT";

    const HW2PATH_CORE = "HW2PATH_CORE";

    const HW2PATH_CORE_ORIGIN = "HW2PATH_CORE_ORIGIN";

    const HW2PATH_FRAMEWORK = "HW2PATH_FRAMEWORK";
    
    //urls
    const HW2PATH_CORE_URL = "HW2PATH_CORE_URL";

    const HW2PATH_ROOT_URL = "HW2PATH_ROOT_URL";

    protected $paths;

    /**
     * We use singleton method instead static class to enable
     * Path switching in runtime
     *
     * @param string $coreAlias            
     * @param type $rootPath
     *            path that will be used as root of others, if no specified use
     *            that from core root
     * @return Paths
     */
    public static function I($coreAlias = null, $rootPath = null, $corePath = null, $corePathOrigin = null, $frameworkPath = null)
    {
        $extraKeys = $rootPath ? Array(
            $rootPath
        ) : Array(
            Core::I($coreAlias)->getRootPath()
        );
        return CoreInstantiator::getInstance(get_class(), $coreAlias, Array(
            $rootPath,
            $corePath,
            $corePathOrigin,
            $frameworkPath
        ), true, null, $extraKeys);
    }

    /**
     *
     * @param type $rootPath
     *            path that will be used as root of others, if no specified use
     *            current working directory
     * @param Paths $copyFrom
     *            copy paths from another source rebuilding with new rootPath (
     *            if instance already exists, this won't work )
     */
    public function __construct($rootPath = null, $corePath = null, $corePathOrigin = null, $frameworkPath = null)
    {
        if (! $rootPath) {
            $rootPath = Core::I($this->getCoreAlias())->getRootPath();
        }
        
        $this->setRootPath($rootPath);
        
        // CORE PATH 
        $this->setPath(self::HW2PATH_CORE, 
            // ROOT + ORIGINAL relative path to the core directory
            // this is the trick to get all paths starting from ROOT
            // of course this requires that all instance of Paths class must have the same structure!
            Array(
                self::key(self::HW2PATH_ROOT),
                $corePath ? FileSysBase::findRelativePath($this->getRootPath(), $corePath) : null
            ), PathType::dir, self::HW2PATH_CORE);
        
        // ORIGIN should be used only in some particular cases since Path system already resolve symlink automatically
        $this->setPath(self::HW2PATH_CORE_ORIGIN, $corePathOrigin ? $corePathOrigin : $this->get(self::key(self::HW2PATH_CORE)), PathType::dir, self::HW2PATH_CORE_ORIGIN);
        
        $this->setPath(self::HW2PATH_FRAMEWORK, $corePathOrigin ? $frameworkPath : $this->get(self::key(self::HW2PATH_CORE)), PathType::dir, self::HW2PATH_FRAMEWORK);
    }

    public function rebasePaths(Paths $copyFrom)
    {
        // set new $rootPath and rebuild all paths based on it
        $this->paths = $copyFrom->getAllPaths();
        $this->setRootPath($copyFrom->getRootPath());
        $this->rebuildPaths();
    }

    public function setRootPath($rootPath)
    {
        $this->setPath(self::HW2PATH_ROOT, $rootPath, PathType::dir, self::HW2PATH_ROOT);
        $path = str_replace(rtrim(FileSysBase::normalizePath($_SERVER["DOCUMENT_ROOT"]), "/"), "", rtrim(FileSysBase::normalizePath($rootPath), "/"));
        $this->setUrl(self::HW2PATH_ROOT_URL, $path, PathType::dir);
    }

    /**
     * short way to get root path
     *
     * @return type
     */
    public function getRootPath()
    {
        return $this->get(self::HW2PATH_ROOT);
    }

    /**
     *
     * @param type $key            
     * @param type $path            
     * @param type $type
     *            ( defined in PathType )
     * @param type $isUrl            
     * @param type $global_var
     *            not suggested since paths could be dynamic
     * @param type $useRealPath            
     * @param bool $checkFile            
     * @param type $rebuild            
     * @return PathInfo
     */
    public function build($key, $path, $type, $isUrl, $global_var = "", $info = null, $useRealPath = true, $checkFile = false, $rebuild = false)
    {
        $tPath = "";
        $target = "";
        $wrapperPath = "";
        if (is_array($path)) {
            $ds = $isUrl ? "/" : DS;
            $num = count($path);
            for ($i = 0; $i < $num; $i ++) {
                if (empty($path[$i]))
                    continue;
                
                $tPath .= PathInfo::partToPath($path[$i], $isUrl, $rebuild);
                
                // if we are not at the end, and this part doesn't end with DS
                // then we can add it
                $tPath .= $i + 1 != $num && substr($path[$i], - 1) != $ds ? $ds : null;
            }
        } else 
            if (is_string($path)) {
                $tPath = $path;
                $path = Array(
                    $path
                );
            } else {
                trigger_error("invalid path of " . $path, E_USER_ERROR);
                exit();
            }
        
        // only for path checks
        if (! $isUrl) {
            $tmp = checkFileEnv($tPath);
            
            if ($tmp == null) {
                if ($checkFile) {
                    debug_print_backtrace();
                    trigger_error("File doesn't exists on 
                        tPath: " . $tPath . " 
                        - path:" . print_r($path), E_USER_ERROR);
                    return false;
                }
            } else {
                $tPath = $tmp["ondisk"];
                $wrapperPath = $tmp["wrapper"];
            }
            
            // resolving symlink and reference giving the real absolute path
            // improve performance and avoid symlink usage when including
            if (! empty($tPath))
                $target = realpath($tPath);
        }
        
        // should be removed, use get function instead
        if (! empty($global_var))
            defined($global_var) or define($global_var, $useRealPath && $target ? $target : $tPath);
        
        return new PathInfo($key, $tPath, $path, $type, $global_var, $isUrl, $info, $target, $wrapperPath, $useRealPath);
    }

    public function getAllPaths()
    {
        return $this->paths;
    }

    /**
     *
     * @param type $key            
     * @param type $rebuild            
     * @return PathInfo
     */
    public function get($key, $rebuild = false)
    {
        $key = PathKey::createSignature($key, true);
        $path = $this->paths["$key"];
        if ($rebuild) {
            $path = $this->build($key, $path->parts, $path->type, $path->isUrl, $path->global_var);
        }
        
        return $path;
    }

    /**
     * create a path
     *
     * @param type $key            
     * @param type $path            
     * @param type $type
     *            ( defined in PathType )
     * @param type $global_var            
     * @param type $info            
     * @param type $useRealPath            
     * @param type $uniqueKey            
     * @param type $fileExists            
     * @return PathInfo
     */
    public function setPath($key, $path, $type = 0, $global_var = "", $info = null, $useRealPath = true, $uniqueKey = true, $fileExists = false)
    {
        return $this->set($key, $this->build($key, $path, $type, false, $global_var, $info, $useRealPath, $fileExists, false), $uniqueKey);
    }

    /**
     *
     * @param type $key            
     * @param type $path            
     * @param type $type
     *            ( defined in PathType )
     * @param type $global_var            
     * @param type $info            
     * @param type $useRealPath            
     * @param type $uniqueKey            
     * @param type $fileExists            
     * @return PathInfo
     */
    public function setUrl($key, $path, $type = 0, $global_var = "", $info = null, $useRealPath = true, $uniqueKey = true, $fileExists = false)
    {
        return $this->set($key, $this->build($key, $path, $type, true, $global_var, $info, $useRealPath, $fileExists, false), $uniqueKey);
    }

    /**
     * mostly used internally, you need PathInfo object
     *
     * @param type $key            
     * @param PathInfo $path            
     * @param type $uniqueKey            
     * @return PathInfo
     */
    public function set($key, PathInfo $path, $uniqueKey = true)
    {
        $key = PathKey::createSignature($key, true);
        if (! $uniqueKey || ! array_key_exists(Array(
            $key
        ), $this->paths)) {
            $this->paths["$key"] = $path;
            return $this->paths["$key"];
        } else
            return false;
    }

    public function rebuildPaths()
    {
        /* @var $path PathInfo */
        foreach ($this->paths as $key => $path) {
            $this->paths[$key] = $this->build($path->key, $path->parts, $path->type, $path->isUrl, $path->global_var, $path->info, $path->isRealPath, false, true);
        }
    }

    /**
     * create a key object
     *
     * @param type $key            
     * @return \Hw2\PathKey
     */
    public static function key($key)
    {
        return new PathKey($key);
    }
    
    // test magic function
    public static function __callStatic($name, $arguments)
    {
        echo $name;
        if (strpos($name, 'HW2PATH_') === 0)
            return self::I()->get($name)->get();
    }
}

class FileSysBase
{

    /**
     *
     * @param type $path            
     * @param type $pattern            
     * @param type $deep
     *            default 0 ( ALL )
     * @return type
     */
    public static function rGlob($path = '', $pattern = '/*/i', $depth = -1)
    {
        $directory = new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new \RecursiveIteratorIterator($directory);
        $iterator->setMaxDepth($depth);
        $res = new \RegexIterator($iterator, $pattern);
        
        return iterator_to_array($res);
    }

    public static function fixNS(&$namespace)
    {
        if (empty($namespace))
            return;
            
            //  replace all "\" with directory separator
        $namespace = preg_replace('#\\\\+#', '\\', $namespace);
        
        if (substr($namespace, - 1, 1) != '\\')
            $namespace .= "\\"; // fix final char of namespace
    }

    /**
     * For now supports only protocol splitting ( see
     * http://php.net/manual/en/wrappers.php )
     *
     * @return Array returns array with splitted path
     */
    public static function parsePath($path)
    {
        // fix slashes with correct directory separator
        $parts = preg_split("#^(.*://)#", $path, - 1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $res = (count($parts) > 1) ? Array(
            "wrapper" => $parts[0],
            "path" => $parts[1]
        ) : Array(
            "path" => $parts[0]
        );
        
        return $res;
    }

    public static function normalizePath($path, $DS = DIRECTORY_SEPARATOR, $trimLast = false)
    {
        // fix slashes with correct directory separator
        $parts = self::parsePath($path);
        
        //  replace all "/" with directory separator
        //  replace all "\" with directory separator
        //  remove slash duplicates
        $parts["path"] = preg_replace(array(
            '#/+#',
            '#\\\\+#',
            '#' . $DS . '+#'
        ), $DS, $parts["path"]);
        
        $res = implode("", $parts);
        
        if ($trimLast)
            $res = rtrim($res, $DS);
        
        return $res;
    }

    public static function fix($path)
    {
        // Sanity check 
        if ($path == "") {
            return false;
        }
        
        // Converts all "\" to "/", and erases blank spaces at the beginning and the ending of the string 
        $path = trim(preg_replace("/\\\\/", "/", (string) $path));
        
        /*  Checks if last parameter is a directory with no slashs ("/") in the end. To be considered a dir,  
        *   it can't end on "dot something", or can't have a querystring ("dot something ? querystring") 
        */
        if (! preg_match("/(\.\w{1,4})$/", $path) && ! preg_match("/\?[^\\/]+$/", $path) && ! preg_match("/\\/$/", $path)) {
            $path .= '/';
        }
        
        /**
         * Breaks the original string in to parts: "root" and "dir".
         *
         * "root" can be "C:/" (Windows), "/" (Linux) or
         * "http://www.something.com/" (URLs). This will be the start of output
         * string.
         * "dir" can be "Windows/System", "root/html/examples/",
         * "includes/classes/class.validator.php", etc.
         */
        preg_match_all("/^(\\/|\w:\\/|(http|ftp)s?:\\/\\/[^\\/]+\\/)?(.*)$/i", $path, $matches, PREG_SET_ORDER);
        
        $path_root = $matches[0][1];
        $path_dir = $matches[0][3];
        
        /*  If "dir" part has one or more slashes at the beginning, erases all. 
        *   Then if it has one or more slashes in sequence, replaces for only 1. 
        */
        $path_dir = preg_replace(array(
            "/^\\/+/",
            "/\\/+/"
        ), array(
            "",
            "/"
        ), $path_dir);
        
        // Breaks "dir" part on each slash 
        $path_parts = explode("/", $path_dir);
        
        // Creates a new array with the right path. Each element is a new dir (or file in the ending, if exists) in sequence. 
        for ($i = $j = 0, $real_path_parts = array(); $i < count($path_parts); $i ++) {
            if ($path_parts[$i] == '.') {
                continue;
            } else 
                if ($path_parts[$i] == '..') {
                    if ((isset($real_path_parts[$j - 1]) && $real_path_parts[$j - 1] != '..') || ($path_root != "")) {
                        array_pop($real_path_parts);
                        $j --;
                        continue;
                    }
                }
            
            array_push($real_path_parts, $path_parts[$i]);
            $j ++;
        }
        
        return $path_root . implode("/", $real_path_parts);
    }

    public static function findRelativePath($to, $from)
    {
        if ($to == "" || $from == "") {
            return false;
        }
        
        $to = self::fix($to);
        $from = self::fix($from);
        
        if ($to == $from)
            return "";
        
        preg_match_all("/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i", $to, $matches_1, PREG_SET_ORDER);
        preg_match_all("/^(\\/|\w:\\/|https?:\\/\\/[^\\/]+\\/)?(.*)$/i", $from, $matches_2, PREG_SET_ORDER);
        
        if ($matches_1[0][1] != $matches_2[0][1]) {
            return false;
        }
        
        $path_1_parts = explode("/", $matches_1[0][2]);
        $path_2_parts = explode("/", $matches_2[0][2]);
        
        while (isset($path_1_parts[0]) && isset($path_2_parts[0])) {
            if ($path_1_parts[0] != $path_2_parts[0]) {
                break;
            }
            
            array_shift($path_1_parts);
            array_shift($path_2_parts);
        }
        
        for ($i = 0, $path = ""; $i < count($path_1_parts) - 1; $i ++) {
            $path .= "../";
        }
        
        return $path . implode("/", $path_2_parts);
    }

    public static function getPhpClasses($php_code)
    {
        $classes = array();
        $ns = "";
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        
        for ($i = 0; $i < $count; $i ++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                for ($j = $i + 1; $j < $count; ++ $j) {
                    if ($tokens[$j][0] === T_STRING)
                        $ns .= "\\" . $tokens[$j][1];
                    elseif ($tokens[$j] === '{' or $tokens[$j] === ';')
                        break;
                }
                $i = $j;
            } elseif ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE) {
                $class = $extends = $implements = null;
                $startsExt = $startsImp = $endClass = false;
                for ($j = $i + 1; $j < $count && $j != $i; $j ++) {
                    // reverse order of checks
                    if ($tokens[$j] === '{') {
                        $endClass = true;
                        // fix extends
                        $extends = preg_replace('/\s+/', '', $extends);
                        if (! empty($extends) && substr($extends, 0, 1) != "\\") {
                            $extends = $ns . "\\" . $extends;
                        }
                        // fix implements
                        $parts = explode(",", preg_replace('/\s+/', '', $implements));
                        $cnt = count($parts);
                        $implements = "";
                        for ($p = 0; $p < $cnt; $p ++) {
                            $implements .= (! empty($parts[$p]) && substr($parts[$p], 0, 1) != "\\" ? $ns . "\\" . $parts[$p] : $parts[$p]) . ",";
                        }
                        $implements = trim($implements, ",");
                        
                        // fix class
                        $class = $ns . "\\" . preg_replace('/\s+/', '', $class);
                        
                        $classes[] = Array(
                            "class" => $class,
                            "extends" => $extends,
                            "implements" => $implements
                        );
                        $i = $j + 1;
                    }
                    
                    if ($tokens[$j][0] == T_IMPLEMENTS) {
                        $startsExt = ! $startsImp = true;
                    } elseif ($startsImp && ! $endClass) {
                        if (! is_array($tokens[$j]))
                            $implements .= $tokens[$j];
                        elseif ($tokens[$j][0] != T_WHITESPACE)
                            $implements .= $tokens[$j][1];
                    }
                    
                    if ($tokens[$j][0] == T_EXTENDS) {
                        $startsExt = true;
                    } elseif ($startsExt && ! $endClass) {
                        if ($tokens[$j][0] != T_WHITESPACE)
                            $extends .= $tokens[$j][1];
                    }
                    
                    if (! $startsExt && ! $startsImp && ! $endClass) {
                        if ($tokens[$j][0] != T_WHITESPACE)
                            $class .= $tokens[$j][1];
                    }
                }
            }
        }
        return $classes;
        
        /*
          for ($i = 2; $i < $count; $i++) {
          if ($tokens[$i - 2][0] == T_CLASS
          && $tokens[$i - 1][0] == T_WHITESPACE
          && $tokens[$i][0] == T_STRING) {

          $class_name = $tokens[$i][1];
          $classes[] = $class_name;
          }
          }
          return $classes; 
        */
    }
} 

