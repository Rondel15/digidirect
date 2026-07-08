/* eslint no-unused-vars: [1] */
var config = {
    config: {
        mixins: {
            'Magento_SalesRule/js/view/payment/discount-messages': {
                'Digidirect_ExtendedShippingRates/js/view/payment/discount-message-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Digidirect_ExtendedShippingRates/js/view/shipping-mixin': true
            }
        }
    }
};
