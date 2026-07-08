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
            cartPriceRuleMessageText: window.checkoutConfig.remove_item_rule_message,
            cartPriceRuleMessageType: 'info',
            cartPriceRuleMessageContainerClassName: 'cartrule-message',
            cartPriceRuleMessageContainerLocation: '.column.main'
        },
        initialize: function () {
            this._super();

            var notice = this.cartPriceRuleMessageText,
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
                    if (value.extension_attributes !== undefined && value.extension_attributes.is_remove_item_rule_applied) {
                        isRemoveItemRuleApplied = true;
                        return true;
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
                    }
                } else {
                    $container.empty();
                }
            }, this);

            return this;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
