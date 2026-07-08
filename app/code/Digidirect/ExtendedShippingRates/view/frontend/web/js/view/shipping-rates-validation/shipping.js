/*browser:true*/
/*global define*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/shipping-rates-validator',
        'Magento_Checkout/js/model/shipping-rates-validation-rules',
        'Digidirect_ExtendedShippingRates/js/model/shipping-rates-validator/shipping',
        'Digidirect_ExtendedShippingRates/js/model/shipping-rates-validation-rules/shipping'
    ],
    function (
        Component,
        defaultShippingRatesValidator,
        defaultShippingRatesValidationRules,
        shippingRatesValidator,
        shippingRatesValidationRules
    ) {
        "use strict";
        defaultShippingRatesValidator.registerValidator('shipping', shippingRatesValidator);
        defaultShippingRatesValidationRules.registerRules('shipping', shippingRatesValidationRules);
        return Component;
    }
);
