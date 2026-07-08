define([
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Customer/js/model/address-list',
    'Digidirect_CheckoutFields/js/model/skip-state'
], function (
    formPopUpState,
    addressList,
    skipState
) {
    'use strict';
    return function (target) {
        return target.extend({
            shippingFields: (window.customCheckoutFieldConfig !== undefined) ? window.customCheckoutFieldConfig.fields['shipping-step'] : {},
            billingFields: (window.customCheckoutFieldConfig !== undefined) ? window.customCheckoutFieldConfig.fields['billing-step'] : {},
            isFormInline: addressList().length === 0,
            validate: function () {
                if (this.isCustomBillingFields()) {
                    return this._super();
                }

                if (!this.isFormInline && this.isCustomShippingField()) {
                    if (formPopUpState.isVisible()) {
                        if (this.isCustomShippingField()) {
                            return false;
                        }
                    } else {
                        if (skipState.getSkipValidation()) {
                            if (this.isCustomCheckoutField()) {
                                return false;
                            }
                        } else {
                            if (!this.isCustomCheckoutField()) {
                                return false;
                            }
                        }
                    }
                }
                return this._super();
            },
            isCustomShippingField: function () {
                return this.shippingFields && this.inputName in this.shippingFields;
            },
            isCustomBillingFields: function () {
                return this.billingFields && this.inputName in this.billingFields;
            },
            isCustomCheckoutField: function () {
                return this.isCustomShippingField() || this.isCustomBillingFields();
            }
        });
    };
});
