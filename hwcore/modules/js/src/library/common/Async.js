'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}common/include.js'
], function () {
    var $ = this;


    /**
     * "duck punching" RSVP
     * 
     */

    /*
     * when calling .then() function from cb*() methods
     * latest argument is be te callback result
     * if not callback defined it's null
     */
    $.RSVP.defer.prototype.cbResolve = function () {
        if (this.promise === null && this._callback) {
            return this._callback.apply(null, arguments);
        } else {
            this.resolve(arguments);
        }
    };

    $.RSVP.defer.prototype.cbReject = function () {
        if (this.promise === null && this._callback) {
            return this._callback.apply(null, arguments);
        } else {
            this.reject(arguments);
        }
    };

    var tmp = $.Promise.prototype["catch"];
    $.Promise.prototype["catch"] = function (err, label) {
        // add marker to notify that exception has been caught
        this._label = "__caught__" + (this._label || "");
        return tmp.call(this, err, label);
    };
    // compatible alias for catch
    $.Promise.prototype.fail = $.Promise.prototype["catch"];

    $.RSVP.configure('instrument', true);
    $.RSVP.configure('instrument-with-stack', true);
    $.RSVP.on('rejected', function (e) {
        // print error only when exception has not been caught using promise methods
        if (!e.label || !e.label.indexOf("__caught__") === 0) {
            console.error("Uncaught exception", e, e.detail.stack);
        }
    });

    /*
     * Adapter class for some Q methods
     */
    return $.Async = $.Class({members: [
            {
                a: ["public", "static"], n: "all", v: function (promises) {
                    return $.Promise.all(promises);
                }
            },
            {
                /**
                 * array of promising-function that should be called sequentially 
                 */
                a: ["public", "static"], n: "sequence", v: function (fnArray) {
                    return fnArray.slice(1).reduce(function (prev, curr) {
                        return prev.then(curr);
                    }, fnArray[0]());
                }
            },
            /*{
             a: ["public", "static"], n: "call", v: function (fn) {
             return this.s.apply(fn, Array.prototype.slice.call(arguments, 1));
             }
             },
             {
             a: ["public", "static"], n: "apply", v: function (fn, args) {
             return $.Q.nfapply(fn, args);
             }
             },*/
            {
                /**
                 * fn: accepts resolve and reject methods as parameter
                 */
                a: ["public", "static"], n: "promise", v: function (fn) {
                    return new $.Promise(fn);
                }
            },
            {
                /**
                 * if callback is defined then promise will not be set 
                 */
                a: ["public", "static"], n: "defer", v: function (callback) {
                    var deferred = $.RSVP.defer();
                    if (callback) {
                        if (typeof callback !== "function")
                            throw new Error("callback type is: " + typeof (callback));
                        deferred._callback = callback;
                        deferred.promise = null;
                    }

                    return deferred;
                }
            }
        ]}
    );
});