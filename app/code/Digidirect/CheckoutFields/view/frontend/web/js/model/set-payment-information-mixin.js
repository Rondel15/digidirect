define([
    'jquery',
    'mage/utils/wrapper',
    'Digidirect_CheckoutFields/js/model/custom-fields-assigner'
], function ($, wrapper, customFieldsAssigner) {
    'use strict';

    return function (placeOrderAction) {
        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            customFieldsAssigner(paymentData);

            return originalAction(messageContainer, paymentData);
        });
    };
});