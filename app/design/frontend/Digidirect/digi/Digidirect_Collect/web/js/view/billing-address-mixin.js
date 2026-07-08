define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list'
    ], function (
        $,
        ko,
        quote,
        checkoutData,
        customer,
        addressList
    ) {
    'use strict';

    return function (target) {
        var addressOptions = addressList().filter(function (address) {
            return address.getType() == 'customer-address';
        });
        return target.extend({
            canUseShippingAddress: function (flag) {
                var result = flag != undefined ? flag : quote.customShipping || quote.isShippingAddressHidden ? false : !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
                return result;
            },
            updateAddress: function () {
                this._super();
                if (!this.source.get('params.invalid') && quote.disableShippingForm()) {
                    this.isAddressDetailsVisible(true);
                    this.canUseShippingAddress(false);
                    this.isAddressSameAsShipping(false);
                }
            },
            initObservable: function () {
                this._super()
                .observe({
                    isCanUseShippingAddress: !quote.customShipping && !quote.isShippingAddressHidden
                });

                quote.billingAddress.subscribe(function (newAddress) {
                    if (quote.isVirtual()) {
                        this.isAddressSameAsShipping(false);
                    } else {
                        if (quote.disableShippingForm() || quote.customShipping) {
                            this.isAddressSameAsShipping(false);
                        } else {
                            this.isAddressSameAsShipping(
                                newAddress != null &&
                                newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                            );
                        }
                    }

                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    } else {
                        this.saveInAddressBook(1);
                    }
                    this.isAddressDetailsVisible(true);
                }, this);
                this.checkCollectionMode();
                return this;
            },
            editAddress: function () {
                this._super();
                this.checkCollectionMode();
            },
            checkCollectionMode: function () {
                if (quote.disableShippingForm()) {
                    this.isAddressSameAsShipping(false);
                }
            },
            cancelAddressEdit: function () {
                this._super();
                if (quote.disableShippingForm() || quote.customShipping) {
                    this.isAddressSameAsShipping(false);
                }
            }
        });
    };
});
