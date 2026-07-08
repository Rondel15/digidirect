define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_SalesRule/js/view/payment/discount',
    'Digidirect_ExtendedShippingRates/js/action/shipping-rates-updater',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/action/get-payment-information'
], function ($, _, $t, discount, ratesUpdater, totals, getPaymentInformationAction) {
    'use strict';

    var mixin = {
        defaults: {
            appliedMessage: $t('Your coupon was successfully applied.'),
            canceledMessage: $t('Your coupon was successfully canceled.'),
            appliedCouponMessage: 'SETCOUPONECODE',
            canceledCouponMessage: 'REMOVECOUPONECODE'
        },

        onHiddenChange: function (isHidden) {
            this.checkErrorMessages();
            this._super(isHidden);
        },

        checkErrorMessages: function () {
            var newMessage = null,
                isApplied,
                errorMessages = this.messageContainer.errorMessages();

            if (_.contains(errorMessages, this.appliedCouponMessage)) {
                newMessage = this.appliedMessage;
                isApplied = true;
            }

            if (_.contains(errorMessages, this.canceledCouponMessage)) {
                newMessage = this.canceledMessage;
                isApplied = false;
                this.reloadOrderSummary();
            }

            if (newMessage) {
                this.replaceMessage(newMessage);
                this.setDiscountState(isApplied);
            }
        },

        replaceMessage: function (newMessage) {
            this.removeAll();
            this.messageContainer.successMessages.push(newMessage);
        },

        setDiscountState: function (isApplied) {
            discount().isApplied(isApplied);
            ratesUpdater.isNeedUpdate(true);
        },

        reloadOrderSummary: function () {
            var deferred = $.Deferred();
            totals.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                totals.isLoading(false);
            });
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
