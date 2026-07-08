define([
    'jquery',
    'jquery/ui'

], function ($) {
    'use strict';

    $.widget('mage.orderReviewCollect', {
        options: {
            shippingMethodSelector: '#shipping-method',
            reviewContainer: '.block-order-details-view',
            shippingMethodContainer: '.box-order-shipping-method',
            shippingAddressContainer: '.box-order-shipping-address',
            billingAddressContainer: '.box-order-billing-address',
            editLinkLabel: 'Edit',
            editLinkUrl: window.checkout.checkoutUrl
        },

        _create: function () {
            var $widget = this;
            $($widget.options.shippingAddressContainer).addClass('collect-shipping');
            $($widget.options.billingAddressContainer).addClass('collect-billing');
            $widget._shippingMethodChange($($widget.options.shippingMethodSelector).val());
            $widget._checkShippingMethod($($widget.options.shippingMethodSelector).val());
            $($widget.options.shippingMethodSelector).on('change', function () {
                $widget._shippingMethodChange($(this).val());
            });
        },

        _shippingMethodChange: function (shippingMethod) {
            if (shippingMethod === 'collect_collect') {
                $(this.options.reviewContainer).addClass('collect-container');
            } else {
                $(this.options.reviewContainer).removeClass('collect-container');
            }
        },

        _checkShippingMethod: function (shippingMethod) {
            if (shippingMethod === 'collect_collect') {
                var editLink = '<a href="' + this.options.editLinkUrl + '" title="' + this.options.editLinkLabel + '">' + this.options.editLinkLabel + '</a>';
                $(this.options.shippingMethodContainer).append(editLink);
                $(this.options.shippingMethodSelector).attr('disabled', true);
            }
        }

    });

    return $.mage.orderReviewCollect;
});
