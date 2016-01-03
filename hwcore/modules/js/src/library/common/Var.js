/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}common/include.js'
], function () {
    var $ = this;
    $.Var = $.Class({members: [
            {
                /**
                 * You should pass an anonymous function that returns var to check:
                 * Var.isset(function () { return a.b.c; })
                 * Not optimized for single var check, use a !== undefined instead
                 * 
                 * NOTE: there is no performance penalty for using try..catch block if the property is set. 
                 * There is a performance impact if the property is unset.
                 */
                a: ["public", "static"], n: "isset", v: function (fn) {
                    var value;
                    try {
                        value = fn();
                    } catch (e) {
                        value = undefined;
                    } finally {
                        return value !== undefined;
                    }
                }
            },
            {
                a: ["public", "static"], n: "isNumeric", v: function (n) {
                    return !isNaN(parseFloat(n)) && isFinite(n);
                }
            }
        ]}
    );
});