hwc.define([
    'hwc!{PATH_JS_LIB}browser/cookie/include.js'
], function () {
    var $ = this;
    $.Browser.Cookie = $.class([
        $.protected({
            name: null,
            value: null,
            type: null, // system, analytic, profiling ecc.
            expires: null,
            path: null,
            domain: null
        }),
        $.public({
            __construct: function (name, value, opt) {
                opt = opt || {};
                this.i.name = name;
                this.i.value = value;
                this.i.type = opt.type;
                this.i.expires = opt.expires;
                this.i.path = opt.path;
                this.i.domain = opt.domain;
            },
            /**
             * 
             * @param {type} value if value passed then update with new one, otherwise just set pre-defined
             * @returns {undefined}
             */
            set: function (value) {
                this.i.value = value === undefined ? this.i.value : value;
                this.s.set(this.i.name, this.i.value, this.i.expires, this.i.path, this.i.domain, this.i.type);
            },
            /**
             * retrieve informations from document object
             */
            get: function () {
                return this.s.get(this.i.name);
            },
            delete: function () {
                this.s.delete(this.i.name, this.i.path, this.i.domain);
            }
        }),
        $.private.static({
            disabled: {},
            pending: []
        }),
        $.public.static({
            changeState: function (disable, type) {
                this._s.disabled[type || 'all'] = disable;
            },
            getState: function (type) {
                return this._.disabled[type || 'all'];
            },
            set: function (name, value, expires, path, domain, type) {
                if (this._s.disabled['all'] || this._s.disabled[type]) {
                    return false;
                }

                var cookie = name + "=" + escape(value) + ";";

                if (expires) {
                    // If it's a date
                    if (expires instanceof Date) {
                        // If it isn't a valid date
                        if (isNaN(expires.getTime()))
                            expires = new Date();
                    }
                    else
                        expires = new Date(new Date().getTime() + parseInt(expires) * 1000 * 60 * 60 * 24);

                    cookie += "expires=" + expires.toGMTString() + ";";
                }

                if (path)
                    cookie += "path=" + path + ";";
                if (domain)
                    cookie += "domain=" + domain + ";";

                document.cookie = cookie;

                return true;
            },
            get: function (name) {
                var value = "; " + document.cookie;
                var parts = value.split("; " + name + "=");
                if (parts.length == 2)
                    return parts.pop().split(";").shift();
            },
            delete: function (name, path, domain) {
                /*if (this._s.disabled['all'] || this._s.disabled[type]) {
                 return false;
                 }*/

                // If the cookie exists
                if (this.s.get(name))
                    this.s.set(name, "", -1, path, domain);

                return true;
            },
            parse: function (cookie) {
                cookie = cookie.split(', ');
                var result = {};
                for (var i = 0; i < cookie.length; i++) {
                    var cur = cookie[i].split('=');
                    result[cur[0]] = cur[1];
                }

                return result;
            }
        })
    ]);
});



