'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/gui/index.js",
    "hwc!{PATH_JS_LIB}application/index.js"
], function () {
    var $ = this;

    return $.Browser.Component = $.public.abstract.class.extends($.Component)(
        $.protected({
            template: null,
            selector: null,
            router: null
        }),
        $.public({
            __construct: function (parent, childs, opt) {
                if (!(opt.template instanceof $.Browser.Template)) {
                    throw new Error("You must pass a Template object in opt parameter");
                }

                this.i.template = opt.template;
                this.i.selector = opt.selector;
                // if not opt.router defined then get router
                // from System or parent component
                this.i.router = opt.router || parent.getRouter();

                this.__super(parent, childs, opt);
            },
            __destruct: function () {
                var css = this.i.template.getCss();
                for (var i = 0; i < css.length; i++) {
                    $.Browser.Loader.removeCss(css[i]);
                }

                var el = $.Browser.JQ(this.i.selector);
                for (var i = 0; i < el.length; i++)
                    el[i].innerHTML = "";

                this.__super();
            },
            init: function () {
                var that = this;


                function tmplReq (tmpl) {
                    var html = tmpl.getHtml();
                    var css = tmpl.getCss();
                    var req = [];
                    // fifo order
                    html && req.push(html);
                    if (css.length > 0)
                        req = req.concat(css);
                    return req;
                }

                return $.Browser.include(tmplReq(this.i.template), null, {selector: this.i.selector})
                    .then(function () {
                        return that.__super();
                    });
            },
            getRouter: function () {
                return this.i.router;
            },
            update: function () {
                this.__super();
            }
        })
        );
});