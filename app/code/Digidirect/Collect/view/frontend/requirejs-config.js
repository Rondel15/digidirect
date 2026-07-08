/* eslint no-unused-vars: [1] */

var config = {
    map: {
        '*': {
            'orderReviewCollect': 'Digidirect_Collect/js/order-review-collect',
            'collectBlock': 'Digidirect_Collect/js/collect-block',
            'collectCart': 'Digidirect_Collect/js/collect-cart',
            'collectModal': 'Digidirect_Collect/js/collect-modal'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/model/quote': {
                'Digidirect_Collect/js/model/quote-mixin': true
            },
            'Magento_Checkout/js/model/checkout-data-resolver': {
                'Digidirect_Collect/js/model/checkout-data-resolver-mixin': true
            },
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Digidirect_Collect/js/model/shipping-save-processor/default-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Digidirect_Collect/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Digidirect_Collect/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Digidirect_Collect/js/view/billing-address-mixin': true
            }
        }
    }
};
