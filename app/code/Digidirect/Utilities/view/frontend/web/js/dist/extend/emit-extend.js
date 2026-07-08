define(["exports"], function (exports) {
    "use strict";

    Object.defineProperty(exports, "__esModule", {
        value: true
    });
    exports.emitExtend = emitExtend;
    function emitExtend(context, type, Constants) {
        // prevent double request
        if (type !== Constants.DATA_FETCH_START) {
            context.currentState = type;
        }
    }
});
