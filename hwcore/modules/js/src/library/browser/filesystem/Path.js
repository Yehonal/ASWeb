/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/filesystem/include.js",
    "hwc!{PATH_JS_LIB}filesystem/Path.js"
], function () {
    var $ = this;
    $.Browser.Path = $.Class({base: $.Path, members: [
            {
                attributes: "static",
                name: "fileExists",
                val: function (url) {
                    var defer = $.Async.defer();

                    var req = new XMLHttpRequest();
                    req.open('GET', url, true);
                    req.onreadystatechange = function () {
                        if (req.readyState == 4 ) {
                            if (req.status==200)
                                defer.resolve();
                            else
                                defer.reject();
                        }
                    };
                    req.send();

                    return defer.promise;
                }
            }
        ]}
    );
});