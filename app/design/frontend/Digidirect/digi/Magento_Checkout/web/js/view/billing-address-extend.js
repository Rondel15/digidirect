define(['Magento_Checkout/js/model/quote'], function (quote) {
    'use strict';
    return function (target) {
        return target.extend({
            defaults: {
                isShippingAddressVisible: !quote.isShippingAddressHidden
            }
        });
    };
});
