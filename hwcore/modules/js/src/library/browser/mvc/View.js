hwc.define([
    "hwc!{PATH_JS_LIB}browser/common/index.js"
], function View () {
    var $ = this;
    $.Browser.View = $.abstract.class.extends($.Browser.Component)(
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

    return $.Browser.View;
});