define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'mage/storage',
    'Magento_GiftCardAccount/js/model/payment/gift-card-messages',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'mage/translate'
], function (
    $,
    quote,
    urlBuilder,
    storage,
    messageList,
    errorProcessor,
    customer,
    fullScreenLoader,
    getPaymentInformationAction,
    totals
) {
    'use strict';

    return function (giftCardCode, giftCardPin, serviceCode, additionalData) {
        var serviceUrl,
            payload,
            message = $.mage.__('Gift Card "%1" was added.');

        if (!customer.isLoggedIn()) {
            serviceUrl = urlBuilder.createUrl('/carts/guest-carts/:cartId/abstractGiftCards', {
                cartId: quote.getQuoteId()
            });
        } else {
            serviceUrl = urlBuilder.createUrl('/carts/mine/abstractGiftCards', {
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
        fullScreenLoader.startLoader();
        storage.post(
            serviceUrl, JSON.stringify(payload)
        ).done(
            function (response) {
                var deferred = $.Deferred();
                if (response) {
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);
                    $.when(deferred).done(function () {
                        totals.isLoading(false);
                    });
                    messageList.addSuccessMessage({'message': message.replace('%1', giftCardCode)});
                    $(document).trigger('gift.card.information.save');
                }
            }
        ).fail(
            function (response) {
                totals.isLoading(false);
                errorProcessor.process(response, messageList);
            }
        ).always(
            function () {
                fullScreenLoader.stopLoader();
            }
        );
    };
});
