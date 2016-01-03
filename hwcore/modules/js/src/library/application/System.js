'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}application/include.js',
    'hwc!{PATH_JS_LIB}application/Component.js'
], function () {
    var $ = this;

    return $.System = $.public.class.extends($.Component).use($.Singleton)(
        $.public({
            /**
             * 
             * @param {String} name
             * @param {String|Object} mod
             * @param {Object} opt
             *  autoStart -> define if this component can be started at boot
             *  
             * @returns boolean
             */
            register: function (name, mod, opt) {
                if (this.i.childs[name]) {
                    throw new Error("A component with same name ( " + name + " ) already exists!");
                }

                this.i.childs[name] = {module: mod, opt: opt};
            },
            unregister: function (name) {
                delete this.i.childs[name];
            },
            loadServices: function () {
                var that = this;

                if (typeof that.i.childs === "object") {
                    var promises = [];
                    for (var key in that.i.childs) {
                        var child = that.i.childs[key];
                        if (child.opt.autoStart) {
                            // load child and bind to event handler
                            promises.push(that.s.load(child.module, key, that.i, null, child.opt, arguments));
                        }
                    }

                    if (promises.length > 0) {
                        return $.Async.all(promises).then(function () {
                            return true;
                        });
                    }
                }

                return true;
            },
            init: function () {
                // overwrite component init without calling it to 
                // selectively load childs
                this.i.loadServices.apply(this, arguments);
            }
        })
        );
});