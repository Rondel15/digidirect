define([
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/model/customer',
    'ko'
], function (checkoutData, customer, ko) {
    'use strict';

    var disableShippingForm = ko.observable(null),
        isSingleCartCollectVariation = window.checkoutConfig.quoteData.is_single_cart_collect_variation;

    return function (target) {
        if (isSingleCartCollectVariation) {
            target.customShipping = false;
            target.isShippingAddressHidden = false;
        } else {
            target.customShipping = window.checkoutConfig.quoteData.collect_items && !window.checkoutConfig.quoteData.delivery_items;
            target.isShippingAddressHidden = checkoutData.getSelectedShippingRate() == 'collect_collect';
        }

        target.disableShippingForm = disableShippingForm;
        target.defaultBillingAddress = customer.getBillingAddressList().filter(function (address) {
            return address.isDefaultBilling();
        })[0];
        return target;
    };
});
