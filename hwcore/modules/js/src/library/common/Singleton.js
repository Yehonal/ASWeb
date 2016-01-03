hwc.define([
    'hwc!{PATH_JS_LIB}common/include.js'
], function () {
    var $ = this;

    // it can be used as trait
    $.Singleton = $.final.class([
        $.protected.static({
            instance: null
        }),
        $.public.static({
            /**
             * helper method to create a singleton for a class
             */
            I: function () {
                var self=this.__caller;
                if (!self.instance) {
                    self.instance = $.ObjUtils.instanceFactory(self, arguments);
                }

                return self.instance;
            }
        })
    ]);
    
    return $.Singleton;
});



