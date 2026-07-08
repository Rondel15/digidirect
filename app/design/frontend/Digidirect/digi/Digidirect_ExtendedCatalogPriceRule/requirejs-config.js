/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'catalogPriceRuleModal': 'Digidirect_ExtendedCatalogPriceRule/js/rule-modal'
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Digidirect_ExtendedCatalogPriceRule/js/extends/swatch-renderer-mixin': true
            },
            'Magento_Catalog/js/price-box': {
                'Digidirect_ExtendedCatalogPriceRule/js/price-box-mixin': true
            }
        }
    }
};
