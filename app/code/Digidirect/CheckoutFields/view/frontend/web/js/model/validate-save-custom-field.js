define([
    'jquery',
    'mage/storage',
    'Magento_Checkout/js/model/resource-url-manager',
    'uiRegistry',
    'Magento_Checkout/js/view/billing-address'
], function ($, storage, resourceUrlManager, registry, billingAddress) {
    'use strict';

    return {
        /**
         * Validate custom checkout fields and save in backend
         *
         * @returns {boolean}
         */
        validate: function () {
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
                    options.label = values.frontend_name;
                    params[index] = options;
                }
            });

            return passed;
        }
    };
});
