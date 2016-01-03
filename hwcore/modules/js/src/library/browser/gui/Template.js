/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/gui/include.js"
], function () {
    var $ = this;


    $.Browser.Template = $.class(
        $.private({
            html: null,
            cssList: null
        }),
        $.public({
            __construct: function (html, css) {
                // we can have only one html per-template, but multiple stylesheets
                this._i.html = html;
                // tricky assignment
                this._i.css = Array.isArray(css) && css || (css && [css] || []);
            },
            getHtml: function () {
                return this._i.html;
            },
            getCss: function () {
                return this._i.css;
            }
        })
    );
});