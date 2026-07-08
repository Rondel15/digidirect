define(['module', 'exports'], function (module, exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _typeof = typeof Symbol === "function" && typeof Symbol.iterator === "symbol" ? function (obj) {
        return typeof obj;
    } : function (obj) {
        return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj;
    };

    var mixin = {
        initialize: function initialize() {
            this._super();
            this.digidirectPopupCustom();

            return this;
        },

        digidirectPopupCustom: function digidirectPopupCustom() {
            if ((typeof this.popUpForm === 'function' || _typeof(this.popUpForm) === 'object' && !!this.popUpForm) && (typeof this.popUpForm.options === 'function' || _typeof(this.popUpForm.options) === 'object' && !!this.popUpForm.options)) {
                this.popUpForm.options.modalClass = ' -content-scroll';
            }

            return this;
        }
    };

    exports.default = function (target) {
        return target.extend(mixin);
    };

    module.exports = exports['default'];
});
