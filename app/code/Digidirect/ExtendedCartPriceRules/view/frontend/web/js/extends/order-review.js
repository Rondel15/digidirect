define([
    'jquery',
    'jquery/ui',
    'Magento_Paypal/order-review'
], function ($) {
    'use strict';

    $.widget('mage.orderReview', $.mage.orderReview, {
        options: {
            isDisabledByCartPriceRule: false
        },
        _create: function () {
            this._super();
            if (this.options.isDisabledByCartPriceRule) {
                this._toggleButton(this.options.orderReviewSubmitSelector, true);
            }
        },
        _updateOrderSubmit: function (shouldDisable, fn) {
            if (this.options.isDisabledByCartPriceRule) {
                this._toggleButton(this.options.orderReviewSubmitSelector, true);
            } else {
                this._super(shouldDisable, fn);
            }
        }
    });

    return $.mage.orderReview;
});
