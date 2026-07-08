define([
    'jquery',
    'jquery/ui',
    'maskPlugin',
    'mage/validation',
    'mage/mage'
], function ($) {
    'use strict';

    $.widget('digidirect.localizationPhone', {
        options: {
            config: {},
            input: '[data-role="localization-input"]',
            select: '[data-role="localization-select"]',
            validateRegex: /^(\d+|[\+ ()])+$/,
            errorMessage: 'Please enter a valid phone number.'
        },

        _create: function () {
            this.targetInput = this.element.find(this.options.input);
            if (!this._hasSavedValue()) {
                this._setFirstData();
            } else {
                this._setSelectedLocale();
            }
            this._bind();
            this._addValidateRule();
        },

        /**
         * Add validate
         * @private
         */
        _addValidateRule: function () {
            var self = this;
            $.validator.addMethod('validate-phone-number', function (value) {
                return value.match(self.options.validateRegex);
            }, $.mage.__(self.options.errorMessage));
        },

        _bind: function () {
            var self = this;
            $(this.options.select).on('change', function () {
                var locale = $(this).val();
                self.targetInput.unmask();
                self.targetInput.val('');
                self._initPlugin(locale);
            });
        },

        /**
         * Checks a saved phone
         * @return {boolean}
         * @private
         */
        _hasSavedValue: function () {
            return !!this.targetInput.val();
        },

        /**
         * Sets the first locale from select
         * @private
         */
        _setFirstData: function () {
            var locale = $(this.options.select).val();
            this._initPlugin(locale);
        },

        /**
         * Sets the locale if phone is saved
         * @private
         */
        _setSelectedLocale: function () {
            var data = this.targetInput.val(),
                locale = this._getSelectedLocale(data);
            if (locale) {
                $(this.options.select).val(locale);
                this._initPlugin(locale);
            } else {
                this._setFirstData();
            }
        },

        /**
         * Finds the selected locale in config
         * @param {string} data
         * @return {string}
         * @private
         */
        _getSelectedLocale: function (data) {
            var n,
                phoneStart;
            
            for (var key in this.options.config) {
                n = this.options.config[key].prefix.length;
                phoneStart = data.substring(0, n);
                
                if (phoneStart === this.options.config[key].prefix) {
                    return key;
                }
            }
        },

        /**
         * Returns the mask from config and sets default prefix
         * @param {string} locale
         * @return {string}
         * @private
         */
        _getConfigMask: function (locale) {
            this.defaultPrefix = this.options.config[locale].prefix;
            return this.options.config[locale].prefix + this.options.config[locale].pattern;
        },

        /**
         * Returns the placeholder from config
         * @param {string} locale
         * @return {object}
         * @private
         */
        _getConfigPlaceholder: function (locale) {
            return this.options.config[locale].prefix + this.options.config[locale].placeholder;
        },

        /**
         * Init the plugin
         * @param {string} locale
         * @private
         */
        _initPlugin: function (locale) {
            var localeData = this.options.config[locale],
                mask,
                placeholder = this._getConfigPlaceholder(locale),
                countryPrefix = localeData.prefix,
                maskConfig = {
                    autoclear: false,
                    placeholder: placeholder,
                    countryPrefix: countryPrefix
                };

            if (localeData.pattern || localeData.placeholder) {
                mask = this._getConfigMask(locale);
            } else {
                mask = localeData.prefix + '?9999999999999999999';
                maskConfig.placeholder = ' ';
            }
            this.targetInput.attr('placeholder', placeholder);
            this.targetInput.mask(mask, maskConfig);
        }
    });

    return $.digidirect.localizationPhone;
});
