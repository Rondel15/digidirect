/*global define,alert*/
define(
    [
        'ko',
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'mage/translate',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'Magento_Ui/js/modal/alert',
        'Magento_Customer/js/customer-data'
    ],
    function (
        ko,
        $,
        quote,
        urlManager,
        errorProcessor,
        messageContainer,
        storage,
        $t,
        getPaymentInformationAction,
        totals,
        alert,
        customerData
    ) {
        'use strict';
        return function (couponCode, isApplied) {
            var quoteId = quote.getQuoteId();
            var url = urlManager.getApplyCouponUrl(couponCode, quoteId);
            var message = $t('Your coupon was successfully applied.');

            return storage.put(
                url,
                {},
                false
            ).done(
                function (response) {
                    if (response) {
                        var deferred = $.Deferred();
                        isApplied(true);
                        totals.isLoading(true);
                        getPaymentInformationAction(deferred);

                        /* Reload cart: */
                        customerData.reload(['cart'], true);
                        /* end */

                        $.when(deferred).done(function () {
                            totals.isLoading(false);
                        });

                        messageContainer.addSuccessMessage({'message': message});
                        if (response != true) {
                            alert({
                                title: $t('Free Gifts'),
                                content: response,
                                actions: {
                                    always: function(){}
                                }
                            });
                        }
                    }
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }
            );
        };
    }
);
