/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/uri/include.js",
    "hwc!{PATH_JS_LIB}uri/Uri.js"
], function () {
    var $ = this;
    return $.Browser.Uri = $.Class({base: $.Uri, members: [
            {
                attributes: "static",
                name: "instance",
                val: null
            },
            {
                attributes: "static",
                name: "I",
                val: function (make_new) {
                    if (make_new || !this._s.instance) {
                        this._s.instance = new this._s(document.location.href);
                    }

                    return this._s.instance;
                }
            }
        ]}
    );
});