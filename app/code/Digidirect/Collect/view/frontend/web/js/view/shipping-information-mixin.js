define(
    [
        'ko',
        'Magento_Checkout/js/model/quote'
    ], function (
        ko,
        quote
    ) {
    'use strict';
    return function (target) {
        return target.extend({
            visible: ko.observable(!quote.isVirtual()),
            isDefaultShipping: !quote.customShipping,
            isShippingSideVisible: ko.observable(!(quote.customShipping || quote.isShippingAddressHidden)),
            initialize: function () {
                this._super();
                quote.disableShippingForm.subscribe(function () {
                    this.isShippingSideVisible(!quote.disableShippingForm());
                }, this);
            }
        });
    };
});
