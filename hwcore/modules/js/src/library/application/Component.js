'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}application/include.js",
    "hwc!{PATH_JS_LIB}event/EventHandler.js"
], function () {
    var $ = this;

    return $.Component = $.public.abstract.class.extends($.Object)(
        $.protected.static({
            stateType: {
                INIT: 0,
                UPDATE: 1
            }
        }),
        $.protected({
            // component tree
            parent: null,
            childs: null,
            eventHandler: null,
            opt: null,
            // mainly needed to differentiate 
            // operations in "build" process
            state: null
        }),
        $.public({
            /**
             * childs: { module: m, opt: o } 
             */
            __construct: function (parent, childs, opt) {
                this.i.childs = childs || [];
                this.i.parent = parent;
                this.i.opt = opt || {};
                this.i.eventHandler = new $.EventHandler();
            },
            init: function () {
                var that = this;
                this.i.state = this.s.stateType.INIT;

                if (Array.isArray(that.i.childs)) {
                    var promises = [];
                    for (var key in that.i.childs) {
                        var child = that.i.childs[key];
                        promises.push(that.s.load(child.module, child.id, that.i, null, child.opt, arguments));
                    }

                    return $.Async.all(promises).then(function () {
                        that.i.build.apply(that, arguments);
                        return that.i;
                    });
                }

                return this.i;
            },
            update: function () {
                this.i.state = this.s.stateType.UPDATE;
                this.i.eventHandler.trigger("update", arguments);

                this.i.build.apply(this, arguments);
            },
            build: function () {

            },
            __destruct: function () {
                this.i.eventHandler.trigger("__destruct");

                //delete this.i.eventHandler;

                this.__super();
            },
            getChild: function (id) {
                return this.i.eventHandler.getChildById(id);
            },
            unbindChild: function (id) {
                var obj = this.i.eventHandler.unbind(id);

                if (obj && obj.__isClass)
                    delete obj.__destruct();
            },
            bindChild: function (id, child) {
                if (!child instanceof $.Component)
                    throw new Error("child is not an instance of Component");

                this.i.eventHandler.bind(child, id);
            }
        }),
        $.public.static({
            /**
             * 
             * @param {String|Object} component : the path of component or the object to initialize
             * @param {String} id (Optional) : undique name to identify component in parent child list
             * @param {type} parent (Optional)
             * @param {type} childs (Optional)
             * @param {type} opt (Optional)
             * @param {type} args (Optional)
             * @returns {Boolean}
             */
            load: function (component, id, parent, childs, opt, args) {
                opt = opt || {};
                function init (M) {
                    if (M.__isClass && M.__isChildOf($.Component)) {
                        var m = new M(parent, childs, opt);

                        if (parent)
                            parent.bindChild(id, m);

                        var test=m.init.apply(m, args);
                        return test;
                    }

                    return false;
                }

                if (typeof component === "string") {
                    return $.Browser.include(component)
                        .then(function (M) {
                            var res = init(M, parent, childs, opt);
                            if (res === false)
                                throw new Error("Passed object is not a Component");

                            return res;
                        });
                } else {
                    return init(component);
                }
            }
        })
        );
});