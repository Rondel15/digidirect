/* global define, mCustomScrollbar */

/**
 * Initialize custom scrollbar
 */
define([
    'jquery',
    'mousewheel',
    'mCustomScrollbar'
], function ($) {
    'use strict';

    $.widget('digidirect.customScrollbar', {
        options: {
            theme: 'dark-3',
            mouseWheel: {
                enable: true
            }
        },

        _create: function () {
            this.element.mCustomScrollbar(this.options);
        },

        destroy: function () {
            this.element.mCustomScrollbar('destroy');
        }
    });

    return $.digidirect.customScrollbar;
});
