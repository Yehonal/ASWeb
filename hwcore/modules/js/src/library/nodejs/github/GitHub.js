hwc.define([
    'https',
    'hwc!{PATH_JS_LIB}nodejs/github/include.js'
], function GitHub (https) {
    var $ = this;

    return $.NodeJs.GitHub = $.class(
        $.private({
            token: null
        }),
        $.public({
            /**
             * 
             * @param {Object} options:
             * {String} auth: must be a string composed by "username:password"
             * {String} token
             * @returns {undefined}
             */
            __construct: function (options) {
                this._i.options = options || {};
            },
            createRepo: function (httpPath, name, callback) {
                
                
                
                var repos;

                var postData = {
                    name: name
                };

                var sData = JSON.stringify(postData);

                var opts = {
                    host: "api.github.com",
                    path: "/"+httpPath + '/repos',
                    method: "POST",
                    //port: 443,
                    headers: {
                        'user-agent': 'NodeJS HTTP Client Path: ' + httpPath + ', Repo: ' + name,
                        'content-length': sData.length,
                        'content-type': 'application/json',
                    }
                };

                this._i.options.token && (opts.headers.Authorization = "token " + this._i.options.token);
                this._i.options.auth && (opts.auth = this._i.options.auth);

                var request = https.request(opts, function (res) {
                    var data = "";
                    res.setEncoding('utf8');

                    res.on('data', function (chunk) {
                        data += chunk;
                    });

                    res.on('end', function () {
                        console.log("headers: ", res.headers);
                        if (res.statusCode >= 400 && res.statusCode < 600 || res.statusCode < 10) {
                            throw new Error("status code: " + res.statusCode + " " + data);
                        } else {
                            repos = JSON.parse(data);
                            console.log(repos);
                            callback && callback(data);
                        }
                    });
                });

                request.write(sData);

                request.on('error', function (e) {
                    console.log('problem with request: ' + e.message);
                });

                request.end();
            }
        })
    );
});
