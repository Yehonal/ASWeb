hwc.define([
    'fs',
    'path',
    'hwc!{PATH_JS_LIB}nodejs/filesystem/include.js'
], function Filesystem (fs, path) {
    var $ = this;

    return $.NodeJs.Filesystem = $.class(
        $.public.static({
            remove: function (target, callback) {
                var deferred = $.Async.defer(callback);

                fs.stat(target, function (err, stats) {
                    if (err) {
                        console.log(JSON.stringify(err));
                        deferred.cbReject();
                    } else {
                        if (stats.isFile()) {
                            fs.unlink(target, function (err) {
                                if (err) {
                                    console.log(JSON.stringify(err));
                                    deferred.cbReject();
                                } else {
                                    deferred.cbResolve();
                                }
                            });

                            return;
                        }

                        if (stats.isDirectory()) {
                            fs.rmdir(target, function (err) {
                                if (err) {
                                    console.log(JSON.stringify(err));
                                    deferred.cbReject();
                                } else {
                                    deferred.cbResolve();
                                }
                            });

                            return;
                        }
                    }
                });

                return deferred.promise;
            },
            /**
             * 
             * @param {type} source
             * @param {type} target
             * @param {type} options
             * keepMTime: true/false
             * @param {type} cb
             * ( you can use Promise return value to avoid callback )
             * @returns {Promise}
             */
            copy: function (source, target, options, cb) {
                var cbCalled = false;
                console.log(cb.name);
                var deferred = $.Async.defer(cb);

                var rd = fs.createReadStream(source);
                rd.on("error", function (err) {
                    done(err);
                });
                var wr = fs.createWriteStream(target);
                wr.on("error", function (err) {
                    done(err);
                });
                wr.on("close", function (ex) {
                    var promises = [];
                    if (options.keepMTime == true) {
                        var deferred = $.Async.defer();
                        promises.push(deferred);
                        var stat = fs.stat(source, function () {
                            fs.utimes(target, stat.atime, stat.mtime, deferred.resolve);
                        });
                    }

                    $.Async.all(promises).then(function () {
                        done();
                    });
                });

                rd.pipe(wr);

                function done (err) {
                    if (!cbCalled) {
                        deferred.cbResolve(err);
                        cbCalled = true;
                    }
                }

                return deferred.promise;
            },
            /**
             * 
             * @param {type} dir
             * @param {type} regExcludes, array of regexp
             * @param {type} done callback function with results as first parameter 
             * ( you can use Promise return value to avoid callback ) 
             * @returns {Promise} 
             */
            walk: function (dir, regExcludes, callback) {
                var deferred = $.Async.defer();

                function cb (err, result) {
                    deferred.cbResolve(err, result);
                }

                _walk(dir, regExcludes, cb);

                function _walk (dir, regExcludes, done) {
                    var results = [];

                    fs.readdir(dir, function (err, list) {
                        if (err)
                            return done(err);

                        var pending = list.length;
                        if (!pending)
                            return done(null, results);

                        list.forEach(function (file) {
                            file = path.join(dir, file);

                            var excluded = false;
                            var len = regExcludes.length;
                            var i = 0;

                            for (; i < len; i++) {
                                if (file.match(regExcludes[i])) {
                                    excluded = true;
                                }
                            }

                            // Add if not in regExcludes
                            if (excluded === false) {
                                results.push(file);

                                // Check if its a folder
                                fs.stat(file, function (err, stat) {
                                    if (stat && stat.isDirectory()) {

                                        // If it is, walk again
                                        _walk(file, regExcludes, function (err, res) {
                                            results = results.concat(res);

                                            if (!--pending) {
                                                done(null, results);
                                            }

                                        });
                                    } else {
                                        if (!--pending) {
                                            done(null, results);
                                        }
                                    }
                                });
                            } else {
                                if (!--pending) {
                                    done(null, results);
                                }
                            }
                        });
                    });
                }

                return deferred.promise;
            }
        })
    );
});
