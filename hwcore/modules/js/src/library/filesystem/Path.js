/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}filesystem/include.js'
],function () {
    var $ = this;
    $.Path = $.Class({members: [
            {
                attributes: "static",
                name: "basename",
                val: function (path) {
                    return path.replace(/\\/g, '/').replace(/.*\//, '');
                }
            },
            {
                attributes: "static",
                name: "dirname",
                val: function (path) {
                    return path.replace(/\\/g, '/').replace(/\/[^\/]*$/, '');
                }
            },
            {
                attributes: "static",
                name: "extension",
                val: function (path) {
                    return (/(?:\.([^.]+))?$/).exec(path)[1];
                }
            }
        ]}
    );
    
    return $.Path;
});