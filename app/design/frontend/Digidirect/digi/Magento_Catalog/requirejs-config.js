var config = {
    config: {
        mixins: {
            'Magento_Catalog/js/product/list/toolbar': {
                'Magento_Catalog/js/product/list/toolbar-mixin': false
            }
        }
    },
    map: {
        '*': {
            'cashbackLabel': 'Magento_Catalog/js/product/price/cashback-label',
            'reposition': 'Magento_Catalog/js/product/price/reposition'
        }
    }
};
