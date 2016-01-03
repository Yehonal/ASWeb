<?php
/**
 *
 * @copyright  Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * @license    GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

namespace Hwc;

defined('HW_CORE_EXEC') or die('Restricted access');

include 'defines.php';

use Hw2\S_Paths as SP;

class S_PlatformLoader extends S_LoaderClass implements S_LoaderInterface {  
    const HW2PATH_SHARE="HW2PATH_SHARE";
    const HW2PATH_SHARE_CONF="HW2PATH_SHARE_CONF";
    const HW2PATH_MODULES="HW2PATH_MODULES";
    const HW2PATH_VENDOR="HW2PATH_VENDOR";
    
    /**
     * 
     * @param type $coreAlias
     * @return S_Loader
     */
    public static function I($coreAlias = null) {
        return parent::I($coreAlias);
    }
        
    public function loadPlatform() {
        // SHARED PATHS
        SP::I()->setPath(self::HW2PATH_SHARE,Array(self::key(self::HW2PATH_CORE),'share'),S_PathType::dir);
        SP::I()->setPath(self::HW2PATH_SHARE_CONF,Array(self::key(self::HW2PATH_SHARE),'configs'),S_PathType::dir);
        SP::I()->setPath(self::HW2PATH_MODULES,Array(self::key(self::HW2PATH_SHARE),'modules'),S_PathType::dir);
        SP::I()->setPath(self::HW2PATH_VENDOR,Array(self::key(self::HW2PATH_SHARE),'vendor'),S_PathType::dir);
        
        S_Factory::I()->build();
        S_Factory::I()->checkDBVersion();
        
        $loader=S_Loader::I();
        //additional libraries
        $loader->addPath("s_apache_thrift",Array(SP::key(SP::HW2PATH_SHARE),'library','apache.thrift'),S_PathType::dir,true,"",S_SiteSide::both);
        $loader->addPath("s_config_lite",Array(SP::key(SP::HW2PATH_SHARE),'library','config_lite'),S_PathType::dir,true,"",S_SiteSide::both);
        //platform
        $loader->addPath("s_modules",Array(SP::key(SP::HW2PATH_SHARE),'modules'),S_PathType::dir);
        $loader->addPath("s_dataset",Array(SP::key(SP::HW2PATH_SHARE),'data','resources','dataset'),S_PathType::dir);
        
        S_SystemEvents::I(S_DS_Builder::cname());
        
        $this->_loadPlugins();
    }   
         
    private function _loadPlugins() {
        HtaccessRewriter::I()->load(S_AppType::web());
        S_Plg_System::I()->load(S_AppType::base());
    }
}
