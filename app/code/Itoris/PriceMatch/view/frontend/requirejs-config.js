/**
 * Copyright © 2018 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
var config = {
    map: {
        '*': {
            'itorispm'         : 'Itoris_PriceMatch/js/itorispm',
            'itorispm-grouped' : 'Itoris_PriceMatch/js/itorispm-grouped'
        }
    },
    config: {
        mixins: {
            'mage/validation': {
                'Itoris_PriceMatch/js/itoris-pm-valdate-price': true
            }
        }
    }
};
