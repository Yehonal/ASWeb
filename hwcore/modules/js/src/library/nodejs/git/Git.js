hwc.define([
    'child_process',
    'fs',
    'hwc!{PATH_JS_LIB}nodejs/git/include.js'
], function Git (child, fs) {
    var $ = this;

    return $.NodeJs.Git = $.class(
        $.public.static({
            exists: function (path, callback) {

                fs.exists(path, function (exists) {
                    if (!exists) {
                        callback(false);
                        return;
                    }

                    var spawn = child.spawn;

                    var rev = spawn('git', ['rev-parse', '--is-inside-work-tree'], {cwd: path});
                    rev.stdout.on('data', function (data) {
                        var res = data.toString().replace(/(\r\n|\n|\r)/gm, "");
                        callback(res === "true");
                    });

                    rev.stderr.on('data', function (data) {
                        callback(false);
                    });
                });
            },
            getCommitHash: function (path, callback) {               
                this.s.exists(path, function (exists) {
                    if (!exists) {
                        callback(false);
                        return;
                    }
                        
                    var spawn = child.spawn;

                    var rev = spawn('git', ['rev-parse', 'HEAD'], {cwd: path});

                    rev.stdout.on('data', function (data) {
                        var res = data.toString().replace(/(\r\n|\n|\r)/gm, "");
                        if (res !== "HEAD")
                            callback(res);
                    });

                    rev.stderr.on('data', function (data) {
                        callback(false);
                    });
                });
            }
        })
    );
});
