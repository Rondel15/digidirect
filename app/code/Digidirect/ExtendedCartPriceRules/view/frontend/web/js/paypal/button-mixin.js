define([
    'jquery',
    'rjsResolver',
    'uiRegistry',
    'domReady!'
], function ($, resolver, registry) {
    'use strict';

    var mixin = {
        defaults: {
            isDisabledByCartPriceRule: false
        },
        getClientConfig: function (data) {
            if (this.isDisabledByCartPriceRule) {
                $('#' + this.id).prop('disabled', true);
            }
            return this._super(data);
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
