/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}browser/common/Browser.js',
    'hwc!{PATH_JS_LIB}browser/common/Loader.js'
],function () {
    var $ = this;
    
    /**
     * Alternatives for loading in PHP-style
     */
    Object.defineProperty($.Browser,"include",{
        configurable: false,
        writable: false,
        value: $.Browser.Loader.load
    });

    Object.defineProperty($.Browser,"require",{
        configurable: false,
        writable: false,
        value: $.Browser.Loader.load
    });

    Object.defineProperty($.Browser,"includeSync",{
        configurable: false,
        writable: false,
        value: $.Browser.Loader.loadSync
    });

    Object.defineProperty($.Browser,"requireSync",{
        configurable: false,
        writable: false,
        value: $.Browser.Loader.loadSync
    });
});