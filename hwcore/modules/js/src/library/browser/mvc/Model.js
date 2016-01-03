hwc.define([
    "hwc!{PATH_JS_LIB}browser/common/index.js"
], function Model () {
    var $ = this;
    $.Browser.Model = $.abstract.class(
        /**
         * Private variables
         */
        $.protected($.Browser.Controller, "controller", null),
        /**
         * Public members
         */
        $.public({
            __construct: function (controller) {
                this.i.controller=controller;
            }
        })
    );

    return $.Browser.Model;
});