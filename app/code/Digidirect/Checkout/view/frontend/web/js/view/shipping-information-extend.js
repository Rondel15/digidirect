define(['Magento_Checkout/js/view/shipping-information', 'Magento_Checkout/js/model/quote'], function (target, quote) {
    'use strict';

    return target.extend({
        defaults: {
            isDefaultShipping: !quote.customShipping
        },
        initialize: function () {
            if (this.isShippingSideVisible == undefined) {
                this.isShippingSideVisible = !quote.isShippingAddressHidden;
            }
            this._super();
        }
    });
});
