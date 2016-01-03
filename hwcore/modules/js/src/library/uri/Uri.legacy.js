'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}uri/include.js'
], function () {
    var $ = this;
    return $.Uri = $.Class({members: [
            /**
             * Private
             */
            {
                a: "private", n: "url", v: null},
            {
                a: "private", n: "parsedUrl", v: null},
            {
                a: "private", n: "parsedQuery", v: null},
            /**
             * Public
             */
            {
                a: "public", n: "__construct", v: function (url, parseFragment) {
                    if (typeof url !== "string")
                        throw new Error("Url must be a string!");

                    this._i.url = url;

                    this._i.parsedUrl = this.s.parseUrl(url);
                    this._i.parsedQuery = this.s.parseQuery(this._i.parsedUrl.query);

                    // useful with single page apps
                    if (parseFragment) {
                        this._i.parsedUrl.fragment = new $.Uri(this._i.parsedUrl.fragment, false);
                    }
                }
            },
            {
                a: "public", n: "toString", v: function () {
                    return this._i.url;
                }
            },
            {
                a: "public", n: "getFragment", v: function () {
                    return this.i.getParsedUrl().fragment;
                }
            },
            {
                a: "public", n: "getParsedUrl", v: function () {
                    return this._i.parsedUrl;
                }
            },
            {
                a: "public", n: "getParsedQuery", v: function () {
                    return this._i.parsedQuery;
                }
            },
            {
                a: "public", n: "getParam", v: function (key) {
                    return this.i.parsedQuery[key];
                }
            },
            {
                a: ["public", "static"], n: "parseQuery", v: function (query) {
                    if (!query)
                        return null;

                    var query_string = {};

                    var vars = query.split("&");
                    for (var i = 0; i < vars.length; i++) {
                        var pair = vars[i].split("=");
                        // If first entry with this name
                        if (typeof query_string[pair[0]] === "undefined") {
                            query_string[pair[0]] = pair[1];
                            // If second entry with this name
                        } else if (typeof query_string[pair[0]] === "string") {
                            var arr = [query_string[pair[0]], pair[1]];
                            query_string[pair[0]] = arr;
                            // If third or later entry with this name
                        } else {
                            query_string[pair[0]].push(pair[1]);
                        }
                    }

                    return query_string;
                }
            },
            // from: http://phpjs.org/functions/parse_url/
            // note: Does not replace invalid characters with '_' as in PHP, nor does it return false with
            // a seriously malformed URL.
            // Besides function name, is essentially the same as parseUri as well as our allowing
            // an extra slash after the scheme/protocol (to allow file:/// as in PHP)
            // 
            // example 1: parseUrl('http://username:password@hostname/path?arg=value#anchor');
            // returns 1: {scheme: 'http', host: 'hostname', user: 'username', pass: 'password', path: '/path', query: 'arg=value', fragment: 'anchor'}
            {
                a: ["public", "static"], n: "parseUrl", v: function (str, mode, component, queryKey) {
                    var query, key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
                        'relative', 'path', 'directory', 'file', 'query', 'fragment'
                    ],
                            parser = {
                                php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                                strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
                                // Added one optional slash to post-scheme to catch file:/// (should restrict this)
                                loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/
                            };

                    mode = mode || 'php';

                    var m = parser[mode].exec(str),
                            uri = {},
                            i = 14;
                    while (i--) {
                        uri[key[i]] = m[i] || ""; // if !m[i] then keep an empty string
                    }

                    if (component) {
                        return uri[component.replace('PHP_URL_', '')
                                .toLowerCase()];
                    }

                    if (mode !== 'php') {
                        var name = queryKey || 'queryKey';
                        parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
                        uri[name] = {};
                        query = uri[key[12]] || '';
                        query.replace(parser, function ($0, $1, $2) {
                            if ($1) {
                                uri[name][$1] = $2;
                            }
                        });
                    }
                    delete uri.source;
                    return uri;
                }
            },
            {
                a: "public static", n: "updateParam", v: function (search, key, value, remove) {
                    key = encodeURI(key);
                    value = encodeURI(value);

                    var kvp = search.split('&');

                    var i = kvp.length;
                    var x;
                    while (i--)
                    {
                        x = kvp[i].split('=');

                        if (x[0] == key)
                        {
                            x[1] = value;
                            kvp[i] = x.join('=');
                            break;
                        }
                    }

                    if (i < 0) {
                        kvp[kvp.length] = [key, value].join('=');
                    }

                    return kvp.join('&');
                }
            }
        ]}
    );
}); 