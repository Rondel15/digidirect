define([
    'mage/utils/wrapper',
    'ko',
    'Lof_Paymentfee/js/action/checkout/cart/totals',
    'Magento_Checkout/js/model/quote'
], function (wrapper, ko, totals, quote) {
    'use strict';

    let isLoading = ko.observable(false);

    return function (selectPaymentMethodAction) {
        return wrapper.wrap(selectPaymentMethodAction, function (originalSelectPaymentMethodAction, paymentMethod) {

            originalSelectPaymentMethodAction(paymentMethod);

            let isEnabled = window.checkoutConfig.lof_paymentfee.isEnabled;
            if (!isEnabled) {
                return;
            }

            let selectedMethod = paymentMethod && paymentMethod.method;

            if (selectedMethod) {
                console.log("Using method from parameter:", selectedMethod);
                console.log("Using method from parameter object:", paymentMethod);
                totals(isLoading, selectedMethod);
            } else {

                setTimeout(function () {
                    let fallbackMethod = quote.paymentMethod() && quote.paymentMethod().method;
                    if (fallbackMethod) {
                        console.log("Using method from quote after delay:", fallbackMethod);
                        totals(isLoading, fallbackMethod);
                    } else {
                        console.warn("No payment method found in parameter or quote.");
                    }
                }, 100);
            }
        });
    };
});
