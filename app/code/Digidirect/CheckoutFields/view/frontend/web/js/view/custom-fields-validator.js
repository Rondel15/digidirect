define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Digidirect_CheckoutFields/js/model/validate-save-custom-field'
    ],
    function (Component, additionalValidators, CheckoutFieldsValidator) {
        'use strict';

        additionalValidators.registerValidator(CheckoutFieldsValidator);
        return Component.extend({});
    }
);
