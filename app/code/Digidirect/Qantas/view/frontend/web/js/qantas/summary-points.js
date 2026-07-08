define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Tax/js/view/checkout/summary/grand-total'
], function ($, Component, ko, qoute, grandtotal) {

    'use strict';

    return Component.extend({
        defaults: {
            template: 'Digidirect_Qantas/qantas/summary-points',
            visible: true
        },

        initialize: function () {
            this._super();
        },

        /**
         * @return {*|String}
         */
        totalPoints: function () {
            
            var qantasTotalPoints = window.checkoutConfig.qantas_total_points;
            return qantasTotalPoints;
        }

    });
});
