'use strict';

hwc.define([
    "hwc!{PATH_JS_LIB}browser/event/include.js"
], function () {
    var $ = this;
    $.Browser.EventHandler = $.Class({members: [
            {
                a: "public static", n: "onBodyLoad", v: function (handler) {
                    if (document.readyState === "complete") {
                        handler();
                        return;
                    }

                    this.s.replaceEventListner(window, "load", handler);
                }
            },
            {
                a: "public static", n: "setEventListner", v: function (element, event, handler, storedOnElem, useCapture) {
                    if (typeof element == "string") {
                        // get the native element
                        element = $.Browser.JQ(element)[0];
                    }

                    if (storedOnElem) {
                        if (element.__hwcRouteHandler)
                            element.removeEventListener(event, element.__hwcRouteHandler);

                        element.__hwcRouteHandler = handler;
                    } else {
                        element.removeEventListener(event, handler);
                    }


                    element.addEventListener(event, handler, useCapture);
                }
            }
        ]}
    );
});