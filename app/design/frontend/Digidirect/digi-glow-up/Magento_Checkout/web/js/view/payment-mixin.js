define([
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Checkout/js/model/payment/method-renderer'
], function (
    Component,
    quote,
    additionalValidators,
    methodRenderer
) {
    'use strict';

    return function (Target) {
        return Target.extend({
            placeOrder: function () {
                const selectedMethod = quote.paymentMethod();

                if (!selectedMethod) {
                    console.warn("No payment method selected.");
                    return false;
                }

                // Get the Knockout renderer (component) for the selected method
                const renderer = methodRenderer.get(selectedMethod.method);

                if (!renderer) {
                    console.warn("No renderer instance found for selected method.");
                    return false;
                }

                // Validate
                if (typeof renderer.validate === 'function' && !renderer.validate()) {
                    console.warn("Validation failed.");
                    return false;
                }

                if (!additionalValidators.validate()) {
                    console.warn("Additional validators failed.");
                    return false;
                }

                // Place the order
                if (typeof renderer.placeOrder === 'function') {
                    return renderer.placeOrder();
                }

                console.warn("No placeOrder function found on renderer.");
                return false;
            }
        });
    };
});
