define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-payment-method',
    'mage/template',
    'text!Digidirect_ExtendedCartPriceRules/template/message.html'
], function ($, ko, _, quote, paymentService, checkoutData, selectPaymentMethodAction, mageTemplate, messageTemplate) {
    'use strict';

    var mixin = {
        defaults: {
            extendRulesData: window.checkoutConfig.extendRulesData,
            limitedPaymentMethods: window.checkoutConfig.paymentLimitedByRules,
            methodContainer: '.payment-method',
            disabledClass: '-disabled',
            messageType: 'notice'
        },

        isLimitChecked: false,

        initialize: function () {
            this._super();
            
            if (this.isRemoveItemRuleApplied()) {
                this.isPlaceOrderActionAllowed(false);
//                this.isEnabledPaymentButton(false);
            }
            quote.totals.subscribe(function () {
                if (this.isRemoveItemRuleApplied()) {
                    this.resolvePaymentMethod(false);
                } else {
                    this.resolvePaymentMethod(quote.billingAddress() != null);
                }
            }, this);
            quote.billingAddress.subscribe(function (address) {
                if (this.isRemoveItemRuleApplied()) {
                    this.isPlaceOrderActionAllowed(false);
                    this.isEnabledPaymentButton(false);
                } else {
                    this.isPlaceOrderActionAllowed(address !== null);
//                    this.isEnabledPaymentButton(address !== null);
                }
            }, this);

            return this;
        },
        isRadioButtonVisible: function () {
            var self = this._super();
            this.checkLimitForMethod();
            return self;
        },
        placeOrder: function (data, event) {
            if (this.isRemoveItemRuleApplied()) {
                if (event) {
                    event.preventDefault();
                }
                this.isPlaceOrderActionAllowed(false);
                return false;
            } else {
                return this._super(data, event);
            }
        },
        selectPaymentMethod: function () {
            this._super();

            if (this.isRemoveItemRuleApplied()) {
                this.isPlaceOrderActionAllowed(false);
//                this.isEnabledPaymentButton(false);
            } else {
                this.isPlaceOrderActionAllowed(quote.billingAddress() != null);
//                this.isEnabledPaymentButton(quote.billingAddress() != null);
            }

            return true;
        },
        resolvePaymentMethod: function (isEnable) {
            var self = this,
                availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

            if (selectedPaymentMethod) {
                availablePaymentMethods.some(function (payment) {
                    if (payment.method == selectedPaymentMethod) {
                        self.isPlaceOrderActionAllowed(isEnable);
//                        self.isEnabledPaymentButton(isEnable);
                    }
                });
            }
        },
        isRemoveItemRuleApplied: function () {
            var isRemoveItemRuleApplied = false;

            $.each(quote.getTotals()()['items'], function (key, value) {
                if (value.extension_attributes !== undefined && value.extension_attributes.is_remove_item_rule_applied) {
                    isRemoveItemRuleApplied = true;
                    return true;
                }
            });

            return isRemoveItemRuleApplied;
        },

        /**
         * Check limitation for method
         */
        checkLimitForMethod: function () {
            var rule;
            if (!this.isLimitChecked && this.isLimitedMethod()) {
                rule = this.getApplyedLimitedRule();
                if (rule) {
                    this.disablePayment(rule);
                }
                this.isLimitChecked = true;
            }
        },

        /**
         * Is limited method
         * @returns {boolean}
         */
        isLimitedMethod: function () {
            return _.contains(this.limitedPaymentMethods, this.getCode());
        },

        /**
         * Get applyed method
         * @returns {object|undefined}
         */
        getApplyedLimitedRule: function () {
            return _.filter(this.extendRulesData, {isEnableUnavailablePaymentMethods: true})[0];
        },

        /**
         * Disable method
         * @param rule
         */
        disablePayment: function (rule) {
            var field = $('#' + this.getCode());
            if (this.isChecked() === this.getCode()) {
                this.unSelectMethod();
            }
            field.prop({disabled: true});
            this.addRuleMessage(field, rule);
        },

        /**
         * Unselect method
         */
        unSelectMethod: function () {
            selectPaymentMethodAction(null);
            checkoutData.setSelectedPaymentMethod(null);
        },

        /**
         * Add method message
         * @param field
         * @param rule
         */
        addRuleMessage: function (field, rule) {
            var tmpl = mageTemplate(messageTemplate, {
                data: {
                    text: rule.messageForUnavailablePaymentMethod,
                    type: this.messageType
                }
            });
            field.closest(this.methodContainer).addClass(this.disabledClass).append(tmpl);
        }

    };

    return function (target) {
        return target.extend(mixin);
    };
});
