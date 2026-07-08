define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_GiftCardAccount/js/model/payment/gift-card-messages',
        'Digidirect_AbstractGiftCard/js/model/gift-card',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/error-processor'
    ],
    function ($, ko, urlBuilder, storage, messageList, abstractGiftCardAccount, customer, quote, errorProcessor) {
        'use strict';

        return {
            isLoading: ko.observable(false),
            check: function (giftCardCode, giftCardPin, serviceCode, additionalData) {
                var self = this,
                    payload,
                    serviceUrl;
                this.isLoading(true);
                if (!customer.isLoggedIn()) {
                    serviceUrl = urlBuilder.createUrl('/carts/guest-carts/:cartId/checkAbstractGiftCard', {
                        cartId: quote.getQuoteId()
                    });
                } else {
                    serviceUrl = urlBuilder.createUrl('/carts/mine/checkAbstractGiftCard', {
                        cartId: quote.getQuoteId()
                    });
                }
                payload = {
                    cartId: quote.getQuoteId(),
                    giftCardAccountData: {
                        service_code: serviceCode,
                        code: giftCardCode,
                        pin: giftCardPin
                    }
                };
                if (additionalData) {
                    $.extend(payload, additionalData);
                }
                messageList.clear();
                storage.post(
                    serviceUrl, JSON.stringify(payload)
                ).done(
                    function (response) {
                        abstractGiftCardAccount.isChecked(true);
                        abstractGiftCardAccount.code(giftCardCode);
                        abstractGiftCardAccount.amount(response);
                        abstractGiftCardAccount.isValid(true);
                    }
                ).fail(
                    function (response) {
                        abstractGiftCardAccount.isValid(false);
                        errorProcessor.process(response, messageList);
                    }
                ).always(
                    function () {
                        self.isLoading(false);
                    }
                );
            }
        };
    }
);
