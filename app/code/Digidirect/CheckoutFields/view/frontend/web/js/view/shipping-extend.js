define([
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Digidirect_CheckoutFields/js/model/skip-state',
    'uiRegistry'
], function (_, quote, skipState, uiRegistry) {
    'use strict';

    return function (target) {
        return target.extend({
            defaults: {
                shippingAddressUiRegistryName: 'checkout.steps.shipping-step.shippingAddress'
            },
            initialize: function () {
                this._super();
                quote.isFormInline = this.isFormInline;
                this.setCheckoutSkipValidation();
            },
            validateShippingInformation: function () {
                var self = this,
                    checkoutFields = window.customCheckoutFieldConfig.fields,
                    isValid = true;
                if (!this.isFormInline && Object.keys(checkoutFields).length > 0 && checkoutFields['shipping-step'] !== undefined) {
                    _.each(checkoutFields['shipping-step'], function (item, key) {
                        if (item.validation && item.area) {
                            uiRegistry.async(self.shippingAddressUiRegistryName + '.' + item.area.fieldset + '.' + key)(function (field) {
                                if (field && !field.validate().valid) {
                                    isValid = false;
                                }
                            });
                        }
                    });
                    if (!isValid) {
                        this.focusInvalid();

                        return isValid;
                    }
                }
                return this._super();
            },
            setCheckoutSkipValidation: function () {
                skipState.setSkipValidation(!this.visible());
                this.visible.subscribe(function (flag) {
                    skipState.setSkipValidation(!flag);
                });
            }
        });
    };
});
