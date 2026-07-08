/* eslint no-unused-vars: [1] */

var config = {
    map: {
        '*': {
            'digidirectStoreCheckout': 'Magento_Checkout/js/dist/common/store',
            'accordionStep': 'Magento_Checkout/js/accordion-step',
            'qantasLoader': 'Magento_Checkout/js/qantas',
            'clickAndCollect': 'Digidirect_Locator/js/view/list'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/minicart': {
                'Magento_Checkout/js/dist/view/minicart-extend': true
            },
            'Magento_Checkout/js/view/shipping-extend': {
                'Magento_Checkout/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/list': {
                'Magento_Checkout/js/view/shipping-address/list-extend': true
            },
            'Magento_Checkout/js/view/summary/abstract-total': {
                'Magento_Checkout/js/view/summary/abstract-total-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Magento_Checkout/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/view/sidebar': {
                'Magento_Checkout/js/view/sidebar-mixin': true
            },
            'Magento_Checkout/js/model/payment/method-group': {
                'Magento_Checkout/js/model/payment/method-group-mixin': true
            },
            'Magento_Checkout/js/sidebar': {
                'Magento_Checkout/js/sidebar-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Magento_Checkout/js/dist/view/shipping-extend': true
            }
        }
    }
};