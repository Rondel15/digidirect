define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Customer/js/model/customer'
    ], function ($, quote, urlBuilder, storage, errorProcessor, customer) {
        'use strict';
        return function (target) {
            return target.extend({
                checkEmailAvailability: function (elem) {
                    var result = this._super();
                    $.when(this.isEmailCheckComplete).always(function () {
                        var serviceUrl;
                        if (!customer.isLoggedIn()) {
                            serviceUrl = urlBuilder.createUrl('/guest-carts/:cartId/payment-information', {
                                cartId: quote.getQuoteId()
                            });
                            return storage.get(
                                serviceUrl, false
                            ).done(function (response) {
                                quote.setTotals(response.totals);
                            }).fail(function (response) {
                                errorProcessor.process(response, messageContainer);
                            });
                        }
                    });
                    return result;
                }
            });
        };
    }
);
