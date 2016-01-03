'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/gui/include.js"
], function () {
    var $ = this;
    $.Browser.DOMTools = $.Class({base: $.Path, members: [
            {
                a: "static", n: "redraw", v: function () {
                    $.Browser.JQ('body').hide();
                    setTimeout(function () {
                        $.Browser.JQ('body').show();
                    }, 0);
                }
            },
            {
                a: "static", n: "removeScrollBar", v: function (selector) {
                    var text = $.Browser.JQ(selector);
                    text.wrapAll('<div style="overflow:hidden; height:' + text.height() + 'px; width:' + text.width() + 'px" />');
                    text.css("width", text.width() + (text.width() - text[0].scrollWidth));
                }
            },
            {
                a: "static", n: "centerImage", v: function (imgSelector) {
                    var img = $.Browser.JQ(imgSelector);
                    // we need to wait image loading
                    img.load(function () {
                        var parent = img.parent();

                        //get the width of the parent
                        var parent_height = parent.height();

                        //get the width of the image
                        var image_height = img.height();

                        //calculate how far from top the image should be
                        var top_margin = (parent_height - image_height) / 2;

                        //and change the margin-top css attribute of the image
                        img.css('margin-top', top_margin);

                        parent.css('text-align', 'center');
                    });
                }
            },
            {
                a: "public static ", n: "cloneId", v: function (idPrefix) {
                    // get the last DIV which ID starts with ^= "idPrefix"
                    var div = $.Browser.JQ('[id^="' + idPrefix + '-"]:last');

                    // Read the Number from that DIV's ID
                    // And increment that number by 1
                    var _t = div.prop("id").split("-");
                    if (_t.length == 1) {
                        num = 1;
                    } else {
                        var num = _t[_t.length - 1];
                        if (!$.Var.isNumeric(num))
                            throw Error("Suffix of '" + idPrefix + "' is not a valid integer value");

                        num++;
                    }

                    // Clone it and assign the new ID
                    return div.clone().prop('id', idPrefix + "-" + num);
                }
            }
        ]}
    );
});