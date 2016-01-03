hwc.define([
    'hwc!{PATH_JS_LIB}common/include.js'
], function () {
    var $ = this;
    
    $.Object = $.Class({members: [
            {
                a: "public", n: "__destruct", v: function () {
                }
            },
            {
                a: ["public","static"], n: "clone", v: function (obj) {
                    return JSON.parse(JSON.stringify(obj));
                }
            }
        ]
    });
});



