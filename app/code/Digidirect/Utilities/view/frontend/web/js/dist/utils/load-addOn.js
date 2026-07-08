define(['exports'], function (exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });
    exports.loadAddOn = loadAddOn;
    /**
     * Load addOn
     * @param params
     * @param context
     * @param filePath
     * @param moduleName
     */
    function loadAddOn(params, context) {
        var moduleName = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';

        try {
            var options = params.options;

            if (typeof options === 'undefined') {
                options = params;
            }
            if (options.addOn) {
                require([options.addOn], function (AddOn) {
                    context.addOn = new AddOn(params);
                });
            }
        } catch (e) {
            console.warn(moduleName + ' AddOn load failed', e);
        }
    }
});
