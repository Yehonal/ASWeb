'use strict';

hwc.include([
    'hwc!{PATH_JS_LIB}application/index.js',
    'hwc!{PATH_JS_LIB}browser/router/index.js'
]).define(function () {
    var $ = this;

    return $.Browser.System = $.public.class.extends($.System)(
        $.protected({
            router: null
        }),
        $.public({
            __construct: function (isSpa) {
                this.i.router = $.Browser.Router.I(document.location.href, isSpa);

                this.i.router.addListner(this.i);

                this.__super();
            },
            init: function () {
                this.__super();
            },
            getRouter: function () {
                return this.i.router;
            }
        })
        );
});