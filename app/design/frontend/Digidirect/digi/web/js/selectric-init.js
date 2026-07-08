/**
 * Initialize custom select
 */
define([
    'jquery',
    'domReady',
    'jquery/ui',
    'selectric'
], function ($, domReady) {
    'use strict';

    $.widget('digidirect.customSelect', {
        options: {},
        _init: function () {
            this.element.selectric(this.options);
        }
    });

    return $.digidirect.customSelect;
});
