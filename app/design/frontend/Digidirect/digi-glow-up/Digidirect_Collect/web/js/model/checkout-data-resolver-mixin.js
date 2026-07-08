define([
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/select-billing-address'
], function (
    quote,
    selectBillingAddress
) {
    'use strict';
    return function (target) {
        target.applyBillingAddress = function () {
            var shippingAddress;
            if (quote.customShipping && !quote.canApplyBillingAddress) return;

            if (quote.billingAddress()) {
                selectBillingAddress(quote.billingAddress());
                return;
            }
            shippingAddress = quote.shippingAddress();

            if (shippingAddress && shippingAddress.canUseForBilling() &&
                    (shippingAddress.isDefaultShipping() || !(quote.isVirtual() || quote.customShipping || quote.disableShippingForm()))) {
                // set billing address same as shipping by default if it is not empty
                selectBillingAddress(quote.shippingAddress());
            }
        };
        return target;
    };
});
