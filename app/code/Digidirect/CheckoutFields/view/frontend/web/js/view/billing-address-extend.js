define([
    'Magento_Checkout/js/model/quote'
], function (
    quote
) {
    'use strict';
    return function (target) {
        return target.extend({
            isCheckoutBillingFields: !!window.customCheckoutFieldConfig.fields,
            initialize: function () {
                this._super();
                if (!quote.billingSource) {
                    quote.billingSource = this.source;
                }
            },
            validateCustomFields: function () {
                quote.billingSource.set('params.invalid', false);
                quote.billingSource.trigger('payment.data.validate');
                return !quote.billingSource.get('params.invalid');
            }
        });
    };
});
