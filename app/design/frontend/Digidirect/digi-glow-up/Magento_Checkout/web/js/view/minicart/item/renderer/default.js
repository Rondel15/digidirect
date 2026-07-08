define([
    'uiComponent'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/minicart/item/default'
        },

        /**
         * Return item custom options
         */
        getItemOptions: function (item) {
            if (item.options && item.options.length) {
                return item.options;
            }
            return [];
        }
    });
});
