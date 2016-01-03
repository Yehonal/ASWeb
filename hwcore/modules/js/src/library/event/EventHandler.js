hwc.define([
    'hwc!{PATH_JS_LIB}event/include.js',
    "hwc!{PATH_JS_LIB}common/Array.js"
], function () {
    var $ = this;
    return $.EventHandler = $.Class({members: [
            /**
             * Vars
             */
            {
                a: ["protected", "static"], n: "instances", v: new Array()
            },
            {
                // Array object is very fexible, it can contains both associative values and numerical
                // we've just to use specific add/delete/loop operations
                a: "private", n: "triggers", v: new Array()
            },
            /**
             * Methods
             */
            {
                a: "public", n: "__construct", v: function (triggers) {
                    this._i.triggers = triggers || [];
                }
            },
            {
                a: "public", n: "setTriggers", v: function (triggers) {
                    this._i.triggers = triggers;
                }
            },
            {
                a: "public", n: "bind", v: function (obj, id, force) {
                    if (force || (id && !this._i.triggers[id] || this._i.triggers.indexOf(obj) < 0)) {
                        // if id is defined, then use this as array key
                        id && (this._i.triggers[id] = obj) || this._i.triggers.push(obj);
                    }
                }
            },
            {
                a: "public", n: "getChildById", v: function(id) {
                    return this._i.triggers[id];
                }
            },
            {
                a: "public", n: "unbind", v: function (objOrId) {
                    var obj = null;
                    if (typeof objOrId == "string") {
                        obj = this._i.triggers[objOrId];
                        delete this._i.triggers[objOrId];
                    } else {
                        var index = this._i.triggers.indexOf(objOrId);
                        obj = this._i.triggers[index];
                        this._i.triggers.splice(index, 1);
                    }

                    return obj; // return the removed object
                }
            },
            {
                a: "public", n: "trigger", v: function (func /*, arguments */) {
                    var that = this;
                    var res = [];
                    var args = arguments;

                    Object.keys(this._i.triggers).map(function (key) {
                        var f = that._i.triggers[key][func];
                        if (typeof f === 'function')
                            res.push(f.apply(that._i.triggers[key], Array.prototype.slice.call(args, 1)));
                    });

                    return $.Async.all(res);
                }
            },
            {
                a: ["public", "static"], n: "I", v: function (key, triggers) {
                    if (typeof this.s.instances[key] === "undefined") {
                        this.s.instances[key] = new this.__caller(triggers);
                    }

                    return this.s.instances[key];
                }
            }
        ]}
    );
});