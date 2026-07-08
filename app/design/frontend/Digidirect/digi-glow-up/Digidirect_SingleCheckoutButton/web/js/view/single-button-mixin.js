define(['mage/translate', 'Magento_Checkout/js/model/quote', 'jquery'], function ($t, quote, $) {
    'use strict';

    return function (target) {
        return target.extend({
            defaults: {
                template: 'Digidirect_SingleCheckoutButton/button',
                labelButtonShippingStep: $t('Continue'),
                labelButtonPaymentStep: $t('Place Your Order'),
                isOnlyPaymentStep: false,
                shippingFormSelector: '#co-shipping-method-form',
                paymentMethodContainer: '.payment-method',
                defaultButton: '.action.primary',
                placeOrderButton: '#placeOrderBtn',
                brainTreeField: '.hosted-control',
                paymentMethod: '.payment-method-title > .radio',
                braintreeGooglePayButtonContinue: '.braintree-googlepay-button',
            },

            paymentStepAction: function () {
                var self = this,
                    $orderBtn = $('[id="placeOrderBtn"]');

                // main disabling of the button for 1 sec to avoid double click
                $orderBtn.prop('disabled', true);
                setTimeout(function () {
                    //checking if it isn't disabled with main way (observable var)
                    if (!self.isDisabled()){
                        $orderBtn.prop('disabled', false);
                    }
                }, 1000);

                $(this.placeOrderButton).css('pointer-events', 'none');

                $(this.brainTreeField).on('mouseenter', function () {
                    $('#placeOrderBtn').css('pointer-events', 'auto');
                });

                $(this.brainTreeField).on('mouseleave', function () {
                    $('#placeOrderBtn').css('pointer-events', 'auto');
                });

                $(this.brainTreeField).on('touchstart', function () {
                    $('#placeOrderBtn').css('pointer-events', 'auto');
                });

                $(this.paymentMethod).on('click', function () {
                    $('#placeOrderBtn').css('pointer-events', 'auto');
                });

                if (this.checkPaymentMethod() && quote.paymentMethod().method === 'braintree_googlepay') {
                    var googlePayButton = $(this.braintreeGooglePayButtonContinue);
                    if (googlePayButton.length ) {
                        googlePayButton.trigger('click');
                    }
                }
                this._super();
            }
        });
    };
});