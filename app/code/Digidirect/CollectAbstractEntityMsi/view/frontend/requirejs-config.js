/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'collectPlacesAvailability': 'Digidirect_CollectAbstractEntityMsi/js/availability',
        }
    },
    config: {
        mixins: {
            'Magento_Swatches/js/swatch-renderer': {
                'Digidirect_CollectAbstractEntityMsi/js/extends/swatch-renderer': true
            }
        }
    }
};
