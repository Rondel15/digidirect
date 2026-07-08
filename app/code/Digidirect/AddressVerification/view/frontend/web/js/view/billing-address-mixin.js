define(['jquery'], function ($) {
    'use strict';
    return function (target) {
        return target.extend({
            onAddressChange: function (address) {
                if (address.customerAddressId === null) {
                    $(document).trigger('av_address_form_loaded');
                }

                return this._super();
            },

            useShippingAddress: function () {
                if (!this.isAddressSameAsShipping()) {
                    $(document).trigger('av_address_form_loaded');
                }

                return this._super();
            }
        });
    };
});
