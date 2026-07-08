/* eslint no-unused-vars: [1] */
var config = {
    config: {
        mixins: {
            'Digidirect_Collect/js/view/block': {
                'Digidirect_CollectAbstractEntity/js/view/block-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Digidirect_CollectAbstractEntity/js/view/shipping-mixin': true
            },
            'Magento_Checkout/js/model/shipping-rates-validator': {
                'Digidirect_CollectAbstractEntity/js/model/shipping-rates-validator-mixin': true
            }
        }
    }
};
