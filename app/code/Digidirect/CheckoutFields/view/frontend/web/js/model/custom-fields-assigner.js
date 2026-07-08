/*global alert*/
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'uiRegistry',
    'Magento_Checkout/js/view/billing-address'
], function ($, quote ,registry, billingAddress) {
    'use strict';

    return function (paymentData) {
        var config = window.customCheckoutFieldConfig,
            provider = registry.get(config.provider),
            billingStepConfig = config.fields['billing-step'],
            shippingStepConfig = config.fields['shipping-step'],
            fieldsBilling = (billingStepConfig === undefined) ? {} : billingStepConfig,
            fields = (shippingStepConfig === undefined) ? {} : shippingStepConfig,
            passed = true,
            params = {};

        $.extend(fields, fieldsBilling);

        if (Object.keys(fields).length < 0) {
            return passed;
        }

        passed = billingAddress().validateCustomFields();

        if (!passed) {
            return passed;
        }

        $.each(fields, function (index, values) {
            if (typeof provider !== 'undefined' &&
                typeof values.area !== 'undefined' &&
                typeof values.area.custom_scope !== 'undefined' &&
                typeof provider[values.area.custom_scope] !== 'undefined'
            ) {
                var options = {};
                options.value = provider[values.area.custom_scope][index];
                options.code = values.frontend_name;
                params[index] = options;
            }
        });

        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }

        paymentData['extension_attributes']['quote_field_values'] = params;
    };
});
