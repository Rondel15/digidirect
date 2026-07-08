/*global define,alert*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/error-processor',
        'Magento_SalesRule/js/model/payment/discount-messages',
        'mage/storage',
        'Magento_Checkout/js/action/get-payment-information',
        'Magento_Checkout/js/model/totals',
        'mage/translate',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        quote,
        urlManager,
        errorProcessor,
        messageContainer,
        storage,
        getPaymentInformationAction,
        totals,
        $t,
        customerData
    ) {
        'use strict';

        return function (isApplied, isLoading) {
            var quoteId = quote.getQuoteId(),
                url = urlManager.getCancelCouponUrl(quoteId),
                message = $t('Your coupon was successfully removed.');
            messageContainer.clear();

            return storage.delete(
                url,
                false
            ).done(
                function () {
                    var deferred = $.Deferred();
                    totals.isLoading(true);
                    getPaymentInformationAction(deferred);

                    /* Reload cart: */
                    customerData.reload(['cart'], true);
                    /* end */

                    $.when(deferred).done(function () {
                        isApplied(false);
                        totals.isLoading(false);
                    });
                    messageContainer.addSuccessMessage({
                        'message': message
                    });
                }
            ).fail(
                function (response) {
                    totals.isLoading(false);
                    errorProcessor.process(response, messageContainer);
                }
            ).always(
                function () {
                    totals.isLoading(false);
                }
            );
        };
    }
);
