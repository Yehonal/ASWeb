/*
 * Copyright (C) 2007 - 2014 Hyperweb2 All rights reserved.
 * GNU General Public License version 3; see www.hyperweb2.com/terms/
 */

'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/common/Browser.js",
    //"hwc!{PATH_JS_LIB}browser/gui/DOMTools.js",
    "hwc!{PATH_JS_LIB}common/String.js",
    "hwc!{PATH_JS_LIB}filesystem/Path.js"
], function () {
    var $ = this;
    $.Browser.Loader = $.Class({base: $.Loader, members: [
            {
                /**
                 * overwrite Loader.load
                 * @returns {Promise}
                 */
                attributes: ["public", "static"],
                name: "load",
                val: function (src, callback, options) {
                    options = options || {};
                    options.sync = false;
                    return this._s.load(src, callback, options);
                }
            },
            {
                /**
                 * overwrite Loader.loadSync
                 */
                attributes: ["public", "static"],
                name: "loadSync",
                val: function (src, options) {
                    options = options || {};
                    options.sync = true;
                    return this._s.load(src, null, options);
                }
            },
            {
                /**
                 * NOTE: For html files the content will be passed as string to callback function
                 * 
                 * src {String} -> path of resource to load
                 * callback {Function} -> function to cast as callback, if not defined then will be returned a promise
                 * options {Object}:
                 * selector {String} -> select the element[s] 
                 * where the retrieved data must be printed ( only for html )
                 * sync {Boolen} -> load in async/sync mode
                 * force {Boolen} -> force reload of css if already exists
                 * filetype { String } -> you can define file type manually if needed ( html/js etc)
                 */
                attributes: ["private", "static"],
                name: "load",
                val: function (srcList, callback, options) {
                    srcList = Array.isArray(srcList) ? srcList : [srcList];

                    var promises = [];
                    var that = this;
                    srcList.forEach(function (src) {
                        var ftype = options.filetype !== undefined ? options.filetype : $.Path.extension(src);

                        switch (ftype) {
                            case "css":
                                promises.push(that._s.loadCss(src, callback, options.sync, options.force));
                                break;
                            case "js":
                                promises.push(
                                    options.sync ?
                                    that._s.__parent.loadSync(src, options) :
                                    that._s.__parent.load(src, null, options)
                                    );
                                break;
                            case "html" || "htm":
                                var deferred = $.Async.defer();
                                var cb = null;

                                if ($.Var.isset(function () {
                                    return options.selector;
                                })) {
                                    var el = $.Browser.JQ(options.selector);
                                    var size = el.size();

                                    cb = function (responseText, textStatus, jqXHR) {
                                        size--;
                                        // resolve only when all elements
                                        // has been loaded
                                        if (size === 0) {
                                            var insType = options.insType || 'html';

                                            if (['prepend', 'append', 'replaceWith', 'html'].indexOf(insType) < 0)
                                                console.error(insType + " is not a valid inserting function");

                                            el[insType](responseText);

                                            deferred.resolve(arguments);
                                        }
                                    };
                                } else {
                                    cb = function () {
                                        deferred.resolve(arguments);
                                    };
                                }

                                $.Browser.JQ.ajaxSetup({async: !options.sync});

                                $.Browser.JQ.ajax(src).done(cb);

                                $.Browser.JQ.ajaxSetup({async: true});

                                promises.push(deferred.promise);
                                break;
                            default:
                                console.error("filetype: " + ftype + " for file " + src + " not supported!");
                                promises.push(false);
                        }

                    });

                    var res = $.Async.all(promises);

                    if (callback) {
                        res.then(function () {
                            callback.apply(null, arguments[0]);
                        });
                    } else {
                        return $.Async.promise(function (resolve) {
                            res.then(function () {
                                resolve.apply(null, arguments[0]);
                            });
                        });
                    }
                }
            },
            {
                attributes: ["private", "static"],
                name: "loadCss",
                val: function (path, fn, sync, force, scope) {
                    var deferred = $.Async.defer();
                    var timeOut = 15000;
                    var timeout_id, interval_id;

                    var head = document.getElementsByTagName('head')[0], // reference to document.head for appending/ removing link nodes
                        link = document.createElement('link');           // create the link node
                    link.setAttribute('href', path);
                    link.setAttribute('rel', 'stylesheet');
                    link.setAttribute('type', 'text/css');

                    var hash = $.String.hashCode(path);

                    // if css already exits, avoid to reload it
                    var old = $.Browser.JQ("#" + hash);
                    if (old.size() > 0) {
                        if (!force) {
                            deferred.resolve(true);
                            return deferred.promise;
                        }
                    }

                    // set id as hashcode of path to easy recover it
                    link.setAttribute('id', hash);

                    var sheet, cssRules;
                    // get the correct properties to check for depending on the browser
                    if ('sheet' in link) {
                        sheet = 'sheet';
                        cssRules = 'cssRules';
                    }
                    else {
                        sheet = 'styleSheet';
                        cssRules = 'rules';
                    }

                    old.size() > 0 ? old.replaceWith(link) : head.appendChild(link);

                    function checkLoad () {
                        try {
                            if (link[sheet]) { // SUCCESS! our style sheet has loaded
                                if (timeout_id && interval_id) {
                                    clearInterval(interval_id);                     // clear the counters
                                    clearTimeout(timeout_id);
                                }
                                // insert the link node into the DOM and start loading the style sheet

                                fn && fn.call(scope, link);           // fire the callback with link
                                deferred.resolve(link); // set promise as resolved
                                return true;
                            } else {
                                return false;
                            }
                        } catch (e) {
                            console.log(e);
                            return false;
                        }
                    }

                    function onFailure () {
                        clearInterval(interval_id);            // clear the counters
                        clearTimeout(timeout_id);
                        head.removeChild(link);                // since the style sheet didn't load, remove the link node from the DOM

                        fn && fn.call(scope, false) // fire the callback with success == false

                        deferred.reject(false); // set promise as rejected

                        link = false;
                    }

                    if (sync) {
                        console.warn("Synchronized load of css files is not supported yet, forced to use async.");
                        /*var start = new Date().getTime();
                         while (true) {
                         if (checkLoad()) {
                         return true;
                         }
                         
                         if ((new Date().getTime() - start) > timeOut) {
                         onFailure();
                         return false;
                         }
                         }*/
                    } //else {

                    timeout_id = setTimeout(function () {       // start counting down till fail
                        onFailure();
                    }, timeOut);                                 // how long to wait before failing

                    interval_id = setInterval(function () {                    // start checking whether the style sheet has successfully loaded
                        checkLoad();
                    }, 10); // how often to check if the stylesheet is loaded

                    return deferred.promise;
                    //}
                }
            },
            {
                attributes: ["public", "static"],
                name: "removeCss",
                val: function (filename) {
                    $.Browser.JQ("#" + $.String.hashCode(filename)).remove();
                }
            }
        ]
    });
});

