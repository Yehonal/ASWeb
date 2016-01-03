/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/language/include.js",
    "hwc!{PATH_JS_LIB}browser/router/index.js"
], function () {
    var $ = this;
    $.Browser.Language = $.Class({base: $.Object, use: [$.Singleton], members: [
            {
                a: "private static", n: "langs", v: {"en": "en-GB", "it": "it-IT"}
            },
            {
                a: "private", n: "lang", v: null
            },
            {
                attributes: "public",
                name: "__construct",
                val: function () {
                    this._i.lang = window.localStorage.getItem("lang") || "it";
                    //var lang = $.Browser.Uri.getInstance().getParam("lang");
                    //if (lang)
                    //    this._i.lang = lang;
                }
            },
            {
                attributes: "public",
                name: "changeLang",
                val: function (lang) {
                    if (!lang in this._s.langs)
                        return false;

                    var old = this._i.lang;
                    this._i.lang = lang;

                    $.Browser.Router.I().getRouteInfo().replaceParam("lang",lang);
                    $.Browser.Router.I().update();
                    window.localStorage.setItem("lang", lang);

                    return old;
                }
            },
            {
                attributes: "public",
                name: "getLang",
                val: function () {
                    return this._i.lang;
                }
            }
        ]}
    );
});