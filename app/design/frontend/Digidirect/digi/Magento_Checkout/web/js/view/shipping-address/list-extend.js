define([
    'jquery',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/model/quote'
], function ($, selectShippingAddressAction, checkoutData, formPopUpState, quote) {
    'use strict';

    return function (target) {
        return target.extend({
            selectSelector: '#shipping-address-select',

            selectAddress: function () {
                var component = this.getComponentByIndex();

                if (component) {
                    selectShippingAddressAction(component.address());
                    checkoutData.setSelectedShippingAddress(component.address().getKey());
                }
            },

            editAddress: function () {
                formPopUpState.isVisible(true);
                this.showPopup();
            },

            toggleEditAddress: function (isEditable) {
                if (isEditable) {
                    $('.edit-address-link').show();
                } else {
                    $('.edit-address-link').hide();
                }
            },

            checkEditStatus: function () {
                var self = this,
                    shippingAddress = quote.shippingAddress();

                if (shippingAddress) {
                    self.toggleEditAddress(shippingAddress.isEditable());
                }

                quote.shippingAddress.subscribe(function (address) {
                    self.toggleEditAddress(address.isEditable());
                });
            },

            showPopup: function () {
                $('[data-open-modal="opc-new-shipping-address"]').trigger('click');
            },

            getComponentByIndex: function () {
                var index = $(this.selectSelector + ' option:selected').data('index');

                return this.rendererComponents[index];
            }
        });
    };
});