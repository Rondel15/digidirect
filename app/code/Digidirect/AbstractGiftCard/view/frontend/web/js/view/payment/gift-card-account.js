define([
    'jquery',
    'underscore',
    'ko',
    'Magento_GiftCardAccount/js/view/payment/gift-card-account',
    'Digidirect_AbstractGiftCard/js/action/set-gift-card-information',
    'Digidirect_AbstractGiftCard/js/action/get-gift-card-information',
    'Magento_Checkout/js/model/totals',
    'Digidirect_AbstractGiftCard/js/model/gift-card',
    'mage/validation'
], function (
    $,
    _,
    ko,
    Component,
    setGiftCardAction,
    getGiftCardAction,
    totals,
    abstractGiftCardAccount
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Digidirect_AbstractGiftCard/payment/gift-card-account',
            giftCardPin: '',
            serviceCode: 'default',
            isPin: false,
            isCheckBalance: true,
            isClearFieldAfterSave: false
        },
        abstractGiftCardConfig: window.checkoutConfig.abstractGiftCardServices,
        isLoading: getGiftCardAction.isLoading,
        abstractGiftCardAccount: abstractGiftCardAccount,
        initialize: function () {
            this._super();
            this.bindEvents();
        },
        bindEvents: function () {
          if (this.isClearFieldAfterSave) {
              $(document).on('gift.card.information.save', $.proxy(this.clearFields, this));
          }
        },
        initObservable: function () {
            this._super()
                .observe('giftCartCode')
                .observe('giftCardPin');
            this.giftCardAccount.isDefault(this.showDefaultForm());
            this.giftCardAccount.isVisible(this.showGiftCardBlock());
            return this;
        },
        setGiftCard: function () {
            if (this.validate()) {
                setGiftCardAction(this.giftCartCode(), this.giftCardPin(), this.serviceCode);
            }
        },
        checkBalance: function () {
            if (this.validate()) {
                getGiftCardAction.check(this.giftCartCode(), this.giftCardPin(), this.serviceCode);
            }
        },
        validate: function () {
            var form = '#giftcard-form-' + this.serviceCode;
            return $(form).validation() && $(form).validation('isValid');
        },
        isAbstractConfig: function () {
            return this.abstractGiftCardConfig && !_.isEmpty(this.abstractGiftCardConfig);
        },
        isActiveService: function (code) {
            var currentService;
            if (this.isAbstractConfig()) {
                currentService = _.find(this.abstractGiftCardConfig, function (item, key) {
                    return key === code;
                });
                if (currentService && currentService.active) {
                    return currentService.active;
                }
            }
            return false;
        },
        /**
         * Show native gift card form
         * @returns {*}
         */
        showDefaultForm: function () {
            if (this.isAbstractConfig()) {
                return this.abstractGiftCardConfig.isDefault;
            }
            return true;
        },
        /**
         * Get count of active abstract gift card services
         */
        getActiveServiceCount: function () {
            return _.size(_.find(this.abstractGiftCardConfig, function (item) {
                return item.active !== undefined && item.active;
            }));
        },
        /**
         * Show gift card block
         * @returns {boolean}
         */
        showGiftCardBlock: function () {
            if (this.isAbstractConfig() && !this.showDefaultForm() && this.getActiveServiceCount() === 0) {
                return false;
            }
            return true;
        },
        /**
         * Clear fields
         */
        clearFields: function () {
            this.giftCartCode('');
            if (this.isPin) {
                this.giftCardPin('');
            }
        }
    });
});
