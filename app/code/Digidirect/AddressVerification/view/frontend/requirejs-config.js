/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'addressVerification': 'Digidirect_AddressVerification/js/autocomplete'
        }
    },
    config: {
        mixins: {
            'mage/menu': {
                'Digidirect_AddressVerification/js/lib/mage/menu-mixin': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Digidirect_AddressVerification/js/view/billing-address-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Digidirect_AddressVerification/js/view/shipping-mixin': true
            }
        }
    },
    deps: [
        'babelpolyfill'
    ]
};
