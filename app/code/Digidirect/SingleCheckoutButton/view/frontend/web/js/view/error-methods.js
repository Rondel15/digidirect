define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    ko,
    Component,
    quote,
    $t
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Digidirect_SingleCheckoutButton/error-methods',
            errorPaymentMethodText: $t('Please, select payment method')
        },

        isErrorPaymentMethod: ko.observable(false),

        initialize: function () {
            this._super(); 
            this.bind();

            return this;
        },

        bind: function () {
            var self = this;
            quote.paymentMethod.subscribe(function () {
                self.isErrorPaymentMethod(!self.checkPaymentMethod());
            });
        },

        /**
         * Check payment method is chosen
         * @return {boolean}
         */
        checkPaymentMethod: function () {
            return !!quote.paymentMethod();
        }
    });
});
