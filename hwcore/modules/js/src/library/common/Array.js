'use strict';

hwc.define([
    'hwc!{PATH_JS_LIB}common/include.js'
], function () {
    var $ = this;
    return $.Array = $.Class({members: [
            {
                /**
                 * Destroy all elements of array
                 */
                a: ["public", "static"], n: "clean", v: function (arr) {
                    var what, a = arr, L = a.length, ax;
                    while (L && arr.length) {
                        what = a[--L];
                        while ((ax = arr.indexOf(what)) !== -1) {
                            arr.splice(ax, 1);
                        }
                    }
                    return arr;
                }
            },
            {
                a: ["public", "static"], n: "remove", v: function (arr, obj) {
                    var index = arr.indexOf(5);
                    index > -1 && arr.splice(index, 1);
                }
            },
            {
                /**
                 * A-B ( subtraction between sets )
                 * [TODO] performance improvement
                 */
                a: ["public", "static"], n: "diff", v: function (a, b) {
                    return a.filter(function (i) {
                        return b.indexOf(i) < 0;
                    });
                }
            },
            {
                /**
                 * A ∩ B ( intersection between sets )
                 */
                a: ["public", "static"], n: "intersect", v: function (a, b) {
                    var ret = [];
                    for (var i = 0; i < a.length; i++) {
                        for (var j = 0; j < b.length; j++) {
                            if (a[i] === b[j]) {
                                ret.push(a[i]);
                                break;
                            }
                        }
                    }

                    return ret;
                }
            }


        ]}
    );
});