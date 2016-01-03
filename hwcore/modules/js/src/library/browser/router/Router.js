'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/router/include.js",
    "hwc!{PATH_JS_LIB}browser/uri/Uri.js",
    "hwc!{PATH_JS_LIB}browser/event/EventHandler.js"
], function Router () {
    var $ = this;

    return $.Browser.Router = $.class.extends($.Object).use($.Singleton)(
        /**
         * Private variables
         */
        $.private({
            events: [],
            routeInfo: null,
            isSpa: false,
            eventHandler: false,
            prevRoute: null,
        }),
        /**
         * Internal Class
         */
        $.public.static({RouteInfo: $.class(
                $.private({
                    isSpa: false,
                    route: null,
                    uri: null
                }),
                $.public({
                    __construct: function (url, isSpa) {
                        this._i.uri = new $.Browser.Uri(url || window.location.href);


                        if (isSpa) {
                            this._i.isSpa = true;
                            this._i.uri.fragmentPrefix("!");
                            var fragment = this._i.uri.fragment(true);
                            this._i.route = new $.Browser.Uri(fragment.href());
                        } else {
                            this._i.route = this._i.uri.clone();
                        }
                    },
                    getComponent: function () {
                        return this._i.route.segmentCoded(0);
                    },
                    setComponent: function (component) {
                        this._i.route.segmentCoded(0, component);
                    },
                    getPath: function () {
                        var p = this._i.route.pathname().split("/");
                        return p.slice(1).join("/");
                    },
                    setPath: function (path) {
                        var component = this.i.getComponent();
                        this._i.route.pathname(component + (path ? "/" + path : ""));
                    },
                    getParams: function () {
                        return this._i.route.search(true);
                    },
                    setParams: function (info) {
                        this._i.route.search(info);
                    },
                    addParam: function (name, value) {
                        this._i.route.addSearch(name, value);
                    },
                    /**
                     * 
                     * @param {type} name
                     * @param {type} value (optional)
                     * @returns {undefined}
                     */
                    removeParam: function (name, value) {
                        this._i.route.removeSearch(name, value);
                    },
                    replaceParam: function (name, value) {
                        this._i.route.removeSearch(name);
                        this._i.route.addSearch(name, value);
                    },
                    getRoute: function () {
                        return this._i.route;
                    },
                    getUri: function () {
                        return this._i.uri;
                    },
                    updateUri: function () {
                        if (this._i.isSpa) {
                            this._i.uri.fragment("!" + this._i.route.href());
                        } else {
                            this._i.uri.href(this._i.route.href());
                        }
                    },
                    clone: function () {
                        return new this.s(this._i.uri.href(), this._i.isSpa);
                    }
                })
                )
        }),
        /**
         * Public members
         */
        $.public({
            __construct: function (url, isSpa, disableListner) {
                var that = this;
                this._i.isSpa = isSpa;
                this._i.eventHandler = new $.EventHandler();
                this._i.routeInfo = new this.s.RouteInfo(url, isSpa);

                if (!disableListner) {
                    this.i.runCheckListner();
                }
            },
            getRouteInfo: function () {
                return  this._i.routeInfo;
            },
            getPrevRouteInfo: function () {
                return  this._i.prevRoute;
            },
            getRoute: function () {
                return  this._i.routeInfo.getRoute();
            },
            isSpa: function () {
                return this._i.isSpa;
            },
            addListner: function (obj) {
                this._i.eventHandler.bind(obj);
            },
            removeListner: function (obj) {
                this._i.eventHandler.unbind(obj);
            },
            setRouteByUrl: function (element, uri, callback, reload) {
                var that = this;
                var isRoute = $.typeCompare(this.s.RouteInfo, uri, !reload);

                if (typeof element === 'string') {
                    element = document.querySelector(element);
                }

                if (!element)
                    throw new Error("This element is not valid!");

                if (element.tagName.toUpperCase() === "A") {
                    element.href = isRoute && uri.getUri().toString() || uri;
                }

                function navigate (evt) {
                    evt.preventDefault();
                    that.i.navigateByUrl(uri, callback, reload);
                }

                if (!reload) {
                    $.Browser.EventHandler.setEventListner(element, "click", navigate, true);
                }
            },
            /**
             * 
             * @param {type} element
             * @param {Object} routeInfo 
             * component
             * path
             * params
             * @param {type} reload
             * @returns {undefined}
             */
            setRoute: function (element, opt, callback, reload) {
                var route = this._i.buildRoute(opt);

                this.i.setRouteByUrl(element, route, callback, reload);
            },
            navigateByUrl: function (uri, callback, reload) {
                var that = this;
                // if not reload, then typecompare will throw an error if it's not a RouteInfo obj.
                var isRoute = $.typeCompare(this.s.RouteInfo, uri, reload);

                if (reload) {
                    callback && callback();
                    window.location.assign(isRoute ? uri.getUri().toString() : uri);
                } else {
                    this.i.update(isRoute ? uri : new this.s.RouteInfo(uri, this.i.isSpa()), callback);
                }
            },
            /**
             * 
             * @param {type} opt
             * component
             * path
             * parameters
             * @param {type} reload
             * @returns {undefined}
             */
            navigate: function (opt, callback, reload) {
                var route = this._i.buildRoute(opt);
                this.i.navigateByUrl(route, callback, reload);
            },
            jump: function (h) {
                var top = document.getElementById(h).offsetTop;
                window.scrollTo(0, top);
            },
            /**
             * Alias for history.back
             */
            goBack: function () {
                window.history.back();
            },
            /**
             * Alias for document.referrer;
             * @returns {Node.referrer|String|Document.referrer|document.referrer}
             */
            getPreviousUrl: function () {
                return document.referrer;
            },
            update: function (routeInfo, callback) {
                var that = this;

                this._i.prevRoute = this._i.routeInfo;
                this._i.routeInfo = routeInfo || this._i.routeInfo;

                callback && callback();

                this._i.eventHandler.trigger("beforeUpdate")
                    .then(this._i.eventHandler.trigger("update"))
                    .then(function (results) {
                        /*  for now params should be manually added via RouteInfo
                         var params = {};
                         for (var key in results) {
                         params = $.ObjUtils.merge(params, results[key]);
                         }
                         
                         that._i.routeInfo.setParams(params);
                         */

                        that._i.routeInfo.updateUri();

                        var url = that._i.routeInfo.getUri().href();
                        window.history.pushState(that._i.routeInfo, "Title", url);
                    })
                    .then(this._i.eventHandler.trigger("afterUpdate"));
            },
            refresh: function (forceReload) {
                (forceReload || !this.i.isSpa()) &&
                    window.location.reload() ||
                    this.i.update();
            },
            /**
             * 
             * @param {type} re: regular expression for url
             * @param {type} handler
             */
            addEvent: function (re, handler) {
                if (typeof re == 'function') {
                    handler = re;
                    re = '';
                }

                if (!this._i.events[re])
                    this._i.events[re] = [];

                this._i.events[re].push(handler);
            },
            /**
             * 
             * @param {type} param can be the reference to event function or the regular expression string
             */
            removeEvent: function (param) {
                if (typeof param === "function") {
                    for (var re in this._i.events) {
                        var i = this._i.events[re].indexOf(param);
                        if (i >= 0) {
                            this._i.events[re].splice(i, 1);
                        }
                    }
                } else {
                    delete this._i.events[re];
                }
            },
            clearEvents: function () {
                this._i.events = [];
                return this;
            },
            runCheckListner: function () {
                var that = this;
                /*
                 * back or forward browser events
                 */
                $.Browser.EventHandler.setEventListner(window, "popstate", function () {
                    that.i.pop();
                });
                /**
                 * hash change events
                 */
                $.Browser.EventHandler.setEventListner(window, "hashchange", function () {
                    that.i.checkEvents();
                });

                /**
                 * fist check
                 */
                this.i.checkEvents();
            },
            checkEvents: function (f) {
                var fragment = f || this.i.getRouteInfo().getRoute().href();
                for (var re in this._i.events) {
                    var match = fragment.match(re);
                    if (match) {
                        match.shift();
                        for (var i in this._i.events[re])
                            this._i.events[re][i].apply({}, match);

                        return match;
                    }
                }

                return this;
            },
            pop: function (e) {
                // update routeInfo and cast update trigger
                if (this.i.getRouteInfo().getUri().href() != window.location.href) {
                    // update route info
                    this.i.update(new $.Browser.Router.RouteInfo(window.location.href, this.i.isSpa()));
                    // then check for events
                    this.i.checkEvents();
                }
            }
        }),
        $.private({
            buildRoute: function (opt) {
                opt = opt || {};
                var route = this._i.routeInfo.clone();
                opt.component && route.setComponent(opt.component);
                //opt.path !== undefined && route.setPath(opt.path);
                route.setPath(opt.path || ""); // path should be always resetted
                opt.params !== undefined && route.setParams(opt.params);
                route.updateUri();
                return route;
            }
        })
        );
});