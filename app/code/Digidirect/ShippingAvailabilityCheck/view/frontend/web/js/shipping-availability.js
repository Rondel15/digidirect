define([
    'jquery',
    'underscore',
    'mage/storage',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Customer/js/customer-data',
    'mage/template',
    'text!Digidirect_ShippingAvailabilityCheck/template/shipping-methods.html',
    'mage/translate',
    'loader',
    'jquery/ui',
    'mage/validation'
], function ($, _, storage, urlBuilder, customerData, template, tmpItem) {
    'use strict';

    $.widget('digidirect.shippingAvailability', {
        options: {
            productId: null,
            qty: '#qty',
            countryId: '#country',
            regionId: '[data-role="region_id"]',
            postcode: '[data-role="postcode"]',
            customerId: null,
            button: '[data-role="check-shipping-methods"]',
            superAttributeElement: '.super-attribute-select',
            superAttributeWrapper: '.swatch-attribute',
            superAttributeId: 'attribute-id',
            shippingMethodsContainer: '[data-role="shipping-methods"]',
            emptyShippingListMessage: $.mage.__('There is no available shipping methods for the entered address'),
            fieldsForValidate: '#country',
            validateClass: 'required-entry',
            validatePostcodeMessage: $.mage.__('Please enter a valid zip code.'),
            addToCartButton: '#product-addtocart-button',
            productType: '',
            isInputsNeeded: false,
            grouped: {
                productList: '#super-product-table',
                qtyNameMask: '[name="super_group[&]"]',
                productItem: '[data-product-id]',
                productIdAttribute: 'data-product-id'
            },
            bundle: {
                container: '.bundle-options-wrapper',
                field: '[name^="bundle_option"]'
            },
            giftCard: {
                amountField: '#giftcard-amount-input',
                fieldsContainer: '[data-container-for="giftcard_info"]',
                requiredFields: '.required-entry'
            }
        },

        isErrorAttributes: false,
        isValidationInited: false,

        _create: function () {
            this.bind();
            this.clearValidateAttributes();
            this.initPostcodeValidation();
        },

        /**
         * Bind
         */
        bind: function () {
            $(this.options.button).on('click', $.proxy(function (e) {
                e.preventDefault();
                this.checkFields();
            }, this));

            if ($(this.options.superAttributeElement).length) {
                $(this.options.superAttributeElement).on('change', $.proxy(this.checkSelectedAttributes, this));
            }

            $(this.options.addToCartButton).on('click', $.proxy(this.clearValidation, this));
        },

        initPostcodeValidation: function () {
            var self = this;
            $.validator.addMethod('validate-zip-code', function (v) {
                return $.mage.isEmptyNoTrim(v) || /^[a-zA-Z0-9]+$/.test(v);
            }, function () {
                return self.options.validatePostcodeMessage;
            });
        },

        /**
         * Check validate all required fields
         */
        checkFields: function () {
            var self = this,
                validateElements = $(this.options.fieldsForValidate),
                isValid = true;

            validateElements.each(function () {
                if ($(this).is(':visible')) {
                    $(this).addClass(self.options.validateClass);
                    if (!self.isValidField($(this))) {
                        isValid = false;
                    }
                }
            });

            if (!self.isValidField($(this.options.postcode))) {
                isValid = false;
            }

            if (isValid) {
                this.checkMethods();
            }
        },

        /**
         * Remove default validate attribute
         */
        clearValidateAttributes: function () {
            $(this.options.fieldsForValidate).removeAttr('data-validate');
        },

        /**
         * Validate single filed
         * @param field
         * @return {boolean}
         */
        isValidField: function (field) {
            this.isValidationInited = true;
            return field.validation() && field.validation('isValid');
        },

        /**
         * Remove validation class
         */
        clearValidation: function () {
            if (this.isValidationInited) {
                $(this.options.fieldsForValidate).removeClass(this.options.validateClass);
                this.isValidationInited = false;
            }
        },

        /**
         * Get available shipping methods
         */
        checkMethods: function () {
            var self = this,
                serviceUrl = this.options.customerId ? urlBuilder.createUrl('/shippingavailability/getShippingMethodList', {}) : urlBuilder.createUrl('/shippingavailability/guest/getShippingMethodList', {});
            this.startProcess();
            serviceUrl += '/?' + this.getPayload();

            storage.get(
                serviceUrl
            ).done(function (response) {
                self.insertShippingMethods(response);
                self.reloadCustomerDataMessages();
                self.stopProcess();
            }).fail(function (response) {
                var error = JSON.parse(response.responseText);
                if (error && error.message) {
                    self.messageContainer = {
                        messages: [{
                            type: 'error',
                            text: error.message
                        }]
                    };
                    self.setErrorMessage();
                    self.clearShippingMethodsList();
                }
                self.stopProcess();
            });
        },

        /**
         * Get payload data
         * @return {object}
         */
        getPayload: function () {
            var payload = this.getAddress();

            payload += 'productData[product_id]=' + this.options.productId + '&';

            if ($(this.options.qty).val()) {
                payload += 'productData[params][qty]=' + $(this.options.qty).val() + '&';
            }

            if (this.options.customerId) {
                payload += 'customer_id=' + this.options.customerId + '&';
            }

            switch (this.options.productType) {
                case 'grouped': {
                    payload += this.getGroupedData();
                    break;
                }
                case 'bundle': {
                    payload += this.getBundleData();
                    break;
                }
                case 'giftcard': {
                    payload += this.getGiftCardData();
                    break;
                }
                default: {
                    payload += this.getSuperAttributes();
                    break;
                }
            }

            return payload;
        },

        /**
         * Show loader and disable button
         */
        startProcess: function () {
            $(this.options.shippingMethodsContainer).trigger('processStart');
            $(this.options.button).attr('disabled', true);
        },

        /**
         * Hide loader and enable button
         */
        stopProcess: function () {
            $(this.options.shippingMethodsContainer).trigger('processStop');
            $(this.options.button).attr('disabled', false);
        },

        /**
         * Get data for grouped products
         * @return {object}
         */
        getGroupedData: function () {
            var self = this,
                params = '';

            $(this.options.grouped.productList + ' ' + this.options.grouped.productItem).each(function () {
                var id = $(this).attr(self.options.grouped.productIdAttribute),
                    qtyField = self.options.grouped.qtyNameMask.replace(/&/g, id);
                params += 'productData[params][super_group][' + id + ']=' + $(qtyField).val() + '&';
            });
            return params;
        },

        /**
         * Get data for gift card
         * @return {object}
         */
        getGiftCardData: function () {
            var params = '',
                amountField = $(this.options.giftCard.amountField);
            params += 'productData[params][' + amountField.attr('name') + ']=' + amountField.val() + '&';

            $(this.options.giftCard.fieldsContainer).find(this.options.giftCard.requiredFields).each(function () {
                var filed = $(this);
                params += 'productData[params][' + filed.attr('name') + ']=' + filed.val() + '&';
            })
            return params;
        },

        /**
         * Get data for bundle product
         * @return {object}
         */
        getBundleData: function () {
            var params = '';

            $(this.options.bundle.container + ' ' + this.options.bundle.field).each(function () {
                var item = $(this),
                    option;

                if (item.attr('checked') === 'checked') {
                    option = item.attr('name').replace(/[^0-9]/g, '');
                    params += 'productData[params][bundle_option][' + option + ']=' + item.val() + '&';
                }
            });

            return params;
        },

        /**
         * Get super attributes of products
         * @return {string}
         */
        getSuperAttributes: function () {
            var self = this,
                params = '';

            $(this.options.superAttributeElement).each(function () {
                params += 'productData[params][super_attribute][' + $(this).closest(self.options.superAttributeWrapper).attr(self.options.superAttributeId) + ']=' + $(this).val() + '&';
            });

            return params;
        },

        /**
         * Check super attributes
         */
        checkSelectedAttributes: function () {
            var isSelectedAll = true;

            if (!this.isErrorAttributes) {
                return;
            }

            $(this.options.superAttributeElement).each(function () {
                if (!$(this).val()) {
                    isSelectedAll = false;
                    return false;
                }
            });

            if (isSelectedAll) {
                this.reloadCustomerDataMessages();
                this.isErrorAttributes = false;
            }
        },

        /**
         * Set error message
         */
        setErrorMessage: function () {
            customerData.set('messages', this.messageContainer);
            this.messageContainer = {};
            this.isErrorAttributes = true;
        },

        /**
         * Reload messages
         */
        reloadCustomerDataMessages: function () {
            customerData.reload('messages');
        },

        /**
         * Get address
         * @return {object}
         */
        getAddress: function () {
            var address = '',
                country = $(this.options.countryId).val(),
                postcode = $(this.options.postcode).val(),
                region = $(this.options.regionId).val();

            if (country) {
                address += 'address[country_id]=' + country + '&';
            }

            if (postcode) {
                address += 'address[postcode]=' + postcode + '&';
            }

            if (region) {
                address += 'address[region_id]=' + region + '&';
            }

            return address;
        },

        /**
         * Insert shipping methods to page
         * @param {array} data
         */
        insertShippingMethods: function (data) {
            var formatedData = _.isEmpty(data) ? false : data;
            $(this.options.shippingMethodsContainer).html($(template(tmpItem, {
                data: formatedData,
                isInputsNeeded: this.options.isInputsNeeded,
                emptyListMessage: this.options.emptyShippingListMessage
            })));
        },

        /**
         * Clear methods list
         */
        clearShippingMethodsList: function () {
            $(this.options.shippingMethodsContainer).empty();
        },

        /**
         * Callback after update region
         */
        regionUpdated: function () {
        }
    });

    return $.digidirect.shippingAvailability;
});
