/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'orderReview': 'Digidirect_ExtendedCartPriceRules/js/extends/order-review'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/form/element/email': {
                'Digidirect_ExtendedCartPriceRules/js/view/form/element/email': true
            },
            'Magento_Checkout/js/view/cart/shipping-estimation': {
                'Digidirect_ExtendedCartPriceRules/js/view/cart/shipping-estimation-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Digidirect_ExtendedCartPriceRules/js/view/shipping-mixin': true
            },
            'PayPal_Braintree/js/paypal/button': {
                'Digidirect_ExtendedCartPriceRules/js/paypal/button-mixin': true
            },
            'Magento_Paypal/js/in-context/button': {
                'Digidirect_ExtendedCartPriceRules/js/in-context/button-mixin': true
            },
            'Magento_Checkout/js/view/payment/default': {
                'Digidirect_ExtendedCartPriceRules/js/view/payment/default-mixin': true
            },
            'PayPal_Braintree/js/view/payment/method-renderer/paypal': {
                'Digidirect_ExtendedCartPriceRules/js/view/payment/method-renderer/paypal-mixin': true
            }
        }
    }
};
