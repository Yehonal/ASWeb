'use strict';

hwc.include([
    'hwc!{PATH_JS_LIB}date-time/include.js'
]).define(function () {
    var $ = this;

    $.Date = $.class([
        $.public.static({
            zeroFill: function (i) {
                return (i < 10 ? '0' : '') + i;
            }
        })
    ]);
});


