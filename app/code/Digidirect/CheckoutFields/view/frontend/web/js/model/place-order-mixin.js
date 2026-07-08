define([
    'jquery',
    'mage/utils/wrapper',
    'Digidirect_CheckoutFields/js/model/custom-fields-assigner'
], function ($, wrapper, customFieldsAssigner) {
    'use strict';

    return function (placeOrderAction) {

        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            customFieldsAssigner(paymentData);

            return originalAction(paymentData, messageContainer);
        });
    };
});
