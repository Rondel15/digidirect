define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.giftCard', widget, {

            _create: function () {
                this._super();
                var self = this;
                $(this.options.giftCardFormSelector).on('submit', function (e) {
                    if (self.options.preventSubmit) {
                        e.preventDefault();
                    }
                });
            }
        });

        return $.mage.giftCard;
    };
});

