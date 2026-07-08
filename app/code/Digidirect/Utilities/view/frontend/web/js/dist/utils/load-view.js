define(['exports'], function (exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });
    exports.loadView = loadView;
    /**
     * Load component view/customView
     * @param params
     * @param context
     * @param filePath
     * @param moduleName
     */
    function loadView(params, context, filePath) {
        var moduleName = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';

        try {
            var options = params.options;

            if (typeof options === 'undefined') {
                options = params;
            }
            if (options.viewCustom) {
                filePath = './' + options.viewCustom;
            }
            // dynamic load view
            require([filePath], function (View) {
                context.view = new View(params);
            });
        } catch (e) {
            console.warn(moduleName + ' View load failed', e);
        }
    }
});
