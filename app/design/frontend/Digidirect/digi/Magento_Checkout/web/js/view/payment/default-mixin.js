define(['ko'], function (ko) {
    'use strict';

    var mixin = {
        defaults: {
            isEnabledPaymentButton: ko.observable(true)
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
