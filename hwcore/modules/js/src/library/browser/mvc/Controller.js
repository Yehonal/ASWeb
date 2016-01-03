hwc.define([
    "hwc!{PATH_JS_LIB}browser/common/index.js"
], function Controller () {
    var $ = this;
    $.Browser.Controller = $.abstract.class(
        /**
         * Private variables
         */
        $.protected($.Browser.View, "view", null),
        $.protected($.Browser.Model, "model", null),
        /**
         * Public members
         */
        $.public({
            __construct: function (view,model) {
                this.i.view=view;
                this.i.model=model;
            }
        })
    );

    return $.Browser.Controller;
});