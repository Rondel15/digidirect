define([
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'mage/template',
    'text!Digidirect_ExtendedCartPriceRules/template/message.html'
], function ($, ko, quote, mageTemplate, messageTemplate) {
    'use strict';

    var mixin = {
        defaults: {
            cartPriceRuleHighlightClassName: '-highlight',
            cartPriceRuleMessageText: window.checkoutConfig.remove_item_rule_message,
            cartPriceRuleMessageType: 'info',
            cartPriceRuleMessageContainerClassName: 'cartrule-message',
            cartPriceRuleMessageContainerLocation: '.column.main',
            checkoutButton: '[data-role="proceed-to-checkout"]',
            checkoutInContextPayPalButton: '.cart-summary .paypal.checkout a[data-action]',
            checkoutPayPalButton: '.cart-summary .paypal.checkout [data-action="checkout-form-submit"]',
            braintreePayPalButton: '.cart-summary .paypal.checkout .action-braintree-paypal-logo'
        },
        initialize: function () {
            this._super();

            var self = this,
                notice = this.cartPriceRuleMessageText,
                message = {
                    type: this.cartPriceRuleMessageType,
                    text: notice
                };

            quote.totals.subscribe(function (data) {
                var tmpl,
                    isRemoveItemRuleApplied = false,
                    $container = $('.' + this.cartPriceRuleMessageContainerClassName),
                    $containerLocation = $(this.cartPriceRuleMessageContainerLocation);

                $.each(data['items'], function (key, value) {
                    var $cartItem = $('#cart-item-' + value.item_id);
                    if (value.extension_attributes !== undefined && value.extension_attributes.is_remove_item_rule_applied) {
                        $cartItem.addClass(self.cartPriceRuleHighlightClassName);
                        isRemoveItemRuleApplied = true;
                    } else {
                        $cartItem.removeClass(self.cartPriceRuleHighlightClassName);
                    }
                });

                if (isRemoveItemRuleApplied) {
                    if (notice !== undefined) {
                        tmpl = mageTemplate(messageTemplate, {
                            data: message
                        });
                        if ($container.length) {
                            $container.html(tmpl);
                        } else {
                            $containerLocation.prepend('<div class="' + this.cartPriceRuleMessageContainerClassName + '">' + tmpl + '</div>');
                        }
                        $(this.checkoutButton).prop('disabled', true);
                        $(this.checkoutPayPalButton).prop('disabled', true);
                        $(this.braintreePayPalButton).prop('disabled', true);
                        $(this.checkoutInContextPayPalButton).addClass('disabled');
                    }
                } else {
                    $container.empty();
                    $(this.checkoutButton).prop('disabled', false);
                    $(this.checkoutPayPalButton).prop('disabled', false);
                    $(this.braintreePayPalButton).prop('disabled', false);
                    $(this.checkoutInContextPayPalButton).removeClass('disabled');
                }
            }, this);

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
