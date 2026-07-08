define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/view/shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/view/billing-address'
], function (
    $,
    _,
    ko,
    customer,
    quote,
    setShippingInformationAction,
    shippingInformation,
    stepNavigator,
    billingAddress
) {
    'use strict';
    var disableShippingForm = ko.observable(null),
        isShippingVisible = ko.observable(null),
        isSingleCartCollectVariation = window.checkoutConfig.quoteData.is_single_cart_collect_variation;

    return function (target) {
        return target.extend({
            isShippingAddressVisible: ko.observable(!quote.isShippingAddressHidden),
            isShippingVisible: ko.observable(!(customer.isLoggedIn() && quote.customShipping || customer.isLoggedIn() && quote.isShippingAddressHidden)),
            isDefaultShipping: !quote.customShipping,
            initialize: function () {
                this._super();
                quote.isShippingVisibleDisable = disableShippingForm;
                quote.isShippingVisible = isShippingVisible;

                quote.isShippingVisibleDisable(quote.isShippingAddressHidden);
                quote.isShippingVisible(!(customer.isLoggedIn() && quote.customShipping || customer.isLoggedIn() && quote.isShippingAddressHidden));

                quote.isShippingVisibleDisable.subscribe(function () {
                    this.isShippingAddressVisible(!quote.isShippingVisibleDisable());
                    quote.disableShippingForm(quote.isShippingVisibleDisable());
                }, this);

                quote.isShippingVisible.subscribe(function () {
                    this.isShippingVisible(quote.isShippingVisible());
                    quote.disableShippingForm(!quote.isShippingVisible());
                }, this);

                quote.disableShippingForm(quote.isShippingAddressHidden);

                quote.shippingMethod.subscribe(function () {
                    if (quote.shippingMethod()) {
                        var carrierCode = quote.shippingMethod().carrier_code;
                        if (carrierCode === 'collect') {
                            if (isSingleCartCollectVariation) {
                                quote.isShippingVisible(true);
                                quote.isShippingVisibleDisable(false);
                                quote.isShippingAddressHidden = false;
                            } else {
                                customer.isLoggedIn() ? quote.isShippingVisible(false) : quote.isShippingVisibleDisable(true);
                                quote.isShippingAddressHidden = true;
                            }
                        } else {
                            quote.isShippingVisible(true);
                            quote.isShippingVisibleDisable(false);
                            quote.isShippingAddressHidden = false;
                        }
                    }
                }, this);
            },
            validateShippingInformation: function () {
                var loginFormSelector = 'form[data-role=email-with-possible-login]',
                    emailValidationResult = customer.isLoggedIn();

                if (quote.customShipping) {
                    if (!quote.shippingMethod()) {
                        this.errorValidationMessage('Please specify a shipping method.');
                        return false;
                    }

                    if (!customer.isLoggedIn()) {
                        $(loginFormSelector).validation();
                        emailValidationResult = Boolean($(loginFormSelector + ' input[name=username]').valid());
                    }

                    if (this.isFormInline) {
                        if (!quote.shippingMethod().method_code ||
                                !quote.shippingMethod().carrier_code ||
                                !emailValidationResult) {
                            return false;
                        }
                    }

                    if (!emailValidationResult) {
                        $(loginFormSelector + ' input[name=username]').focus();
                        return false;
                    }

                    return true;
                } else if (isSingleCartCollectVariation && quote.isCollectSelected && _.isEmpty(quote.collectPlaces)) {
                    this.onErrorValidationShippingInformation('collectPlace');

                    return false;
                } else {
                    if (quote.isShippingAddressHidden) {
                        if (!quote.shippingMethod()) {
                            this.errorValidationMessage('Please specify a shipping method.');
                            return false;
                        }

                        if (!emailValidationResult) {
                            $(loginFormSelector).validation();
                            return Boolean($(loginFormSelector + ' input[name=username]').valid());
                        }
                        return true;
                    }
                }

                if (isSingleCartCollectVariation && emailValidationResult && quote.isCollectSelected) {
                    this.source.set('params.invalid', false);
                    this.triggerShippingDataValidateEvent();

                    return !this.source.get('params.invalid');
                }

                return this._super();
            },
            setShippingInformationSuccess: function () {
                this._super();

                if (quote.disableShippingForm()) {
                    billingAddress().checkCollectionMode();
                    quote.canApplyBillingAddress = true;
                }
            },
            onErrorValidationShippingInformation: function (type) {}
        });
    };
});
