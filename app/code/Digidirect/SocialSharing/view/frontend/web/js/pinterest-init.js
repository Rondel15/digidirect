define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.pinterestInit', {
        options: {
            source: '//assets.pinterest.com/js/pinit.js'
        },

        _create: function () {
            window.PinUtils ? this.reinintButton() : this.loadScript(); 
        },

        /**
         * Load script
         */
        loadScript: function () {
            require([this.options.source]);
        },

        /**
         * Reinit button
         */
        reinintButton: function () {
            window.PinUtils.build();
        }
    });

    return $.digidirect.pinterestInit;
});
