define([
    'Digidirect_ExtendedShippingRates/js/action/shipping-rates-updater',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/quote'
], function (ratesUpdater, rateRegistry, quote) {
    'use strict';

    var mixin = {
        initialize: function () {
            this._super();
            this.bindShippingRatesUpdate();
        },
        bindShippingRatesUpdate: function () {
            ratesUpdater.isNeedUpdate.subscribe(function (flag) {
                if (flag) {
                    ratesUpdater.clear();
                    this.updateShippingRatesFromServer();
                }
            }, this);
        },
        updateShippingRatesFromServer: function () {
            var cacheKey = quote.shippingAddress().getCacheKey();
            rateRegistry.set(cacheKey, null);
            quote.shippingAddress.valueHasMutated();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
