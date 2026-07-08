define(["exports"], function (exports) {
    "use strict";

    Object.defineProperty(exports, "__esModule", {
        value: true
    });
    exports.getEnabledModule = getEnabledModule;
    /**
     * Get Enabled Module
     * @param name
     */
    function getEnabledModule(name) {
        var modulesArray = window.enabledModules;
        if (modulesArray && modulesArray.length) {
            return modulesArray.indexOf(name) != -1;
        }
    }
});
