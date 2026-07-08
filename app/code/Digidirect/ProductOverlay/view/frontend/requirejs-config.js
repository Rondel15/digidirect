/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'productOverlay': 'Digidirect_ProductOverlay/js/overlay'
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Digidirect_ProductOverlay/js/swatch-overlay': true
            }
        }
    }
};
