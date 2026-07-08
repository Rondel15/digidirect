define([], function () {
    'use strict';

    return function (target) {
        return target.extend({
            initObservable: function () {
                return this._super().observe('isShippingStep');
            }
        });
    };
});
