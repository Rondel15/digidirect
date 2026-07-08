define(function () {
    'use strict';

    return function (target) {
        var validateFields = target.validateFields;
        target.validateFields = function () {
            if (window.checkoutConfig.collectSkipValidate) {
                window.checkoutConfig.collectSkipValidate = false;
                return;
            }
            var result = validateFields.apply(this, arguments);
            return result;
        };
        return target;
    };
});
