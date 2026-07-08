define([
    'jquery',
    'regionUpdater',
    'jquery/ui',
    'shippingAvailability'
], function ($) {
    'use strict';

    $.widget('mage.shippingRegionUpdater', $.mage.regionUpdater, {
        options: {
            isSpecifiedFieldsRequired: true,
            shippingAvailabilityForm: '[data-role="shipping-availability-check"]'
        },

        _checkRegionRequired: function (country) {
            if (!this.options.isSpecifiedFieldsRequired) {
                this._super(country);
            }
        },

        _updateRegion: function (country) {
            this._super(country);
            if (this.options.regionJson[country] && this.options.optionalRegionAllowed) {
                $(this.options.regionListId).removeAttr('disabled');
            }
            $(this.options.shippingAvailabilityForm).shippingAvailability('regionUpdated');
        }
    });

    return $.mage.shippingRegionUpdater;
});
