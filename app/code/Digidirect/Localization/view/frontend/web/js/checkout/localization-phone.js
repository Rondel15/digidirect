define([
    'jquery',
    'underscore',
    'ko',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/validation/validator',
    'maskPlugin'
], function ($, _, ko, Abstract, validator) {
    'use strict';

    return Abstract.extend({
        defaults: {
            template: 'Digidirect_Localization/form/field',
            elementTmpl: 'Digidirect_Localization/form/element/input',
            elementSelectTmpl: 'Digidirect_Localization/form/element/select',
            inputSelector: '[data-role="localization-input"]',
            selectSelector: '[data-role="localization-select"]',
            validateRegex: /^(\d+|[\+ ()])+$/,
            errorMessage: $.mage.__('Please enter a valid phone number.')
        },

        getPlaceholder: ko.observable(null),

        initialize: function () {
            this._super();
            this.data = window.checkoutLocalizationConfig;
            this._addValidateRule();
        },

        /**
         * Add validate
         * @private
         */
        _addValidateRule: function () {
            var self = this;
            validator.addRule('validate-phone-number', function (value) {
                return value.match(self.validateRegex);
            }, self.errorMessage);
            $.extend(this.validation, {'validate-phone-number': {}});
        },

        /**
         * Sets options for select locale
         * @return {array}
         */
        setLocalizationOptions: function () {
            var locales = _.keys(this.data),
                self = this,
                options = _.map(locales, function (locale) {
                    return { locale: locale, countryName: self.getCountryName(locale) };
                });
            return options;
        },

        /**
         *
         * @param locale
         * @returns {*}
         */
        getCountryName: function (locale) {
            return this.data[locale].countryName;
        },

        /**
         * Checks for data availability
         * @return {boolean}
         */
        isLocalizationMode: function () {
            return !_.isEmpty(this.data);
        },

        /**
         *  Runs script after the field rendered
         */
        renderInputComplete: function () {
            if (this.isLocalizationMode()) {
                this.input = $('[data-role="' + this.dataScope + '"]').find(this.inputSelector);
                if (this.value()) {
                    this.setSelectedLocale();
                } else {
                    this.setFirstData();
                }
            }
        },

        /**
         *  Gets select
         */
        renderSelectComplete: function () {
            this.select = $('[data-role="' + this.dataScope + '"]').find(this.selectSelector);
        },

        /**
         * Sets the first locale from select
         */
        setFirstData: function () {
            var locale = this.setLocalizationOptions()[0].locale;
            this.initPlugin(locale);
        },

        /**
         * Sets the locale if phone is saved
         */
        setSelectedLocale: function () {
            var data = this.value(),
                locale = this.getSelectedLocale(data);

            if (locale) {
                this.initPlugin(locale);
                this.select.val(locale);
            } else {
                this.setFirstData();
            }
        },

        /**
         * Finds the selected locale in config
         * @param {string} data
         * @return {string}
         * @private
         */
        getSelectedLocale: function (data) {
            var n,
                phoneStart;

            for (var key in this.data) {
                n = this.data[key].prefix.length;
                phoneStart = data.substring(0, n);

                if (phoneStart === this.data[key].prefix) {
                    return key;
                }
            }
        },

        /**
         * Returns the mask from config and sets default prefix
         * @param {string} locale
         * @return {string}
         */
        getConfigMask: function (locale) {
            this.defaultPrefix = this.data[locale].prefix;
            return this.data[locale].prefix + this.data[locale].pattern;
        },

        /**
         * Returns the placeholder from config
         * @param {string} locale
         * @return {object}
         */
        getConfigPlaceholder: function (locale) {
            return this.data[locale].prefix + this.data[locale].placeholder;
        },

        /**
         * Init the plugin
         * @param {string} locale
         */
        initPlugin: function (locale) {
            var localeData = this.data[locale],
                mask,
                placeholder = this.getConfigPlaceholder(locale),
                countryPrefix = localeData.prefix,
                maskConfig = {
                    autoclear: false,
                    placeholder: placeholder,
                    countryPrefix: countryPrefix
                };
            if (localeData.pattern || localeData.placeholder) {
                mask = this.getConfigMask(locale);
            } else {
                mask = localeData.prefix + '?9999999999999999999';
                maskConfig.placeholder = ' ';
            }
            this.getPlaceholder(placeholder);
            this.input.mask(mask, maskConfig);
        },

        /**
         * Change locale
         */
        changeLocale: function () {
            var locale = this.select.val();
            this.input.unmask();
            this.input.val('');
            this.input.trigger('change');
            this.clearErrors();
            this.initPlugin(locale);
        },
        
        clearErrors: function () {
            this.error(false);
        }
    });
});
