define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    $.widget('digidirect.freeGiftByRules', {
        options: {
            counter: { // rule counter
                wrapper: {}, // rule counter wrapper element
                count_wrapper: {}, // rule counter count wrapper element
                rule_wrapper: {}, // rule wrapper element
                count: 0 // counter buffer of available checked gifts (by rule)
            },
            selectors: { // widget selectors
                counter_count_wrapper: '[data-free-gift-by-rules="count"]', // rule counter count
                counter_rule_wrapper: '[data-free-gift-by-rules="rule-wrapper"]', // rule wrapper
                checkboxes: '[data-free-gift-by-rules="checkbox"]', // gifts checkboxes
                uncheckedItems: '[data-free-gift-by-rules-checked="off"]', // unchecked gifts
                checkedItems: '[data-free-gift-by-rules-checked="on"]', // checked gifts
                total_count: '[data-free-gift-by-rules="total-count"]' // total count of available checked gifts
            },
            formId: 'freegift_items_form', // form id
            ruleId: 0, // rule Id
            ruleDiscountQty: 0, // available quantity of gifts (by rule)
            disableItemClass: '_disabled', // class for disabled gifts
            submitButton: $('[data-free-gift-by-rules="submit"]') // add to cart button
        },

        /**
         * Initialize widget.
         * @private
         */
        _create: function () {
            this._initCounter();
            this._bindUiActions();
            this.toggleDisableItemsProductOptions('disable', 'wc');
            this.options.submitButton.attr('disabled', true);
        },

        /**
         * Initialize rule counter.
         * @private
         */
        _initCounter: function () {
            var counter = this.options.counter,
                selectors = this.options.selectors;

            counter.wrapper = $(this.element);
            counter.count_wrapper = counter.wrapper.find(selectors.counter_count_wrapper);
            counter.rule_wrapper = counter.wrapper.parents(selectors.counter_rule_wrapper);
        },

        /**
         * Initialize UI actions.
         * @private
         */
        _bindUiActions: function () {
            var self = this;

            this.options.counter.rule_wrapper.find(this.options.selectors.checkboxes).on('change', function () {
                if (self.isOneRule($(this))) self.checkStatus($(this));
            });
        },

        /**
         * Checks, if checked/unchecked checkbox has a common rule
         * @param element: checkbox element
         * @returns {boolean}
         */
        isOneRule: function (element) {
            if (+element.data('free-gift-by-rules-rule-id') !== +this.options.ruleId) return false;

            return true;
        },

        /**
         * Checks the checkbox status, and, depending on it, marks(checked/unchecked) the gifts.
         * Checks and sets count
         * @param checkBox - checkbox element
         */
        checkStatus: function (checkBox) {
            var status = checkBox.prop('checked');

            if (status === false) {
                this.toggleCheckedItems(checkBox, 'off');
            } else {
                this.toggleCheckedItems(checkBox, 'on');
            }

            this.checkCount(status);
        },

        /**
         * Checks and sets counts (rule count and total count)
         * Disable/enable gifts if needed
         * @param status - true/false
         */
        checkCount: function (status) {
            var count = this.options.counter.count;

            switch (status) {
                case false:
                    if (count == 0) return;

                    if (count - 1 >= 0) {
                        this.setRuleCount('-');
                        this.setTotalCount('-');
                        this.toggleDisableItems('enable');
                    } else {
                        this.toggleDisableItems('disable');
                    }
                    break;
                default:
                    if (count + 1 == this.options.ruleDiscountQty) {
                        this.setTotalCount('+');
                        this.setRuleCount('+');
                    }

                    if (count + 1 < this.options.ruleDiscountQty) {
                        this.setTotalCount('+');
                        this.setRuleCount('+');
                        this.toggleDisableItems('enable');
                    } else {
                        this.toggleDisableItems('disable');
                    }
                    break;
            }
        },

        /**
         * Marks(checked/unchecked) gifts, disable/enable options
         * @param checkBox - checkbox element
         * @param status - off/on
         */
        toggleCheckedItems: function (checkBox, status) {
            var item = checkBox.closest('[data-free-gift-by-rules-checked]');

            item.attr('data-free-gift-by-rules-checked', status);

            if (status === 'off') {
                $('#' + this.options.formId).validation('clearError');
                item.find('.mage-error').remove();
                this.toggleDisableItemsProductOptions('disable', 'wc', item);

                return;
            }

            this.toggleDisableItemsProductOptions('enable', 'wc', item);
        },

        /**
         * Disable/enable gifts
         * @param status - disable/enable
         */
        toggleDisableItems: function (status) {
            var uncheckedItems = this.options.counter.rule_wrapper.find(this.options.selectors.uncheckedItems),
                widgetCheckboxes = uncheckedItems.find(this.options.selectors.checkboxes);

            if (status === 'disable') {
                uncheckedItems.addClass(this.options.disableItemClass);
                widgetCheckboxes.attr('disabled', true);
                return;
            }

            uncheckedItems.removeClass(this.options.disableItemClass);
            widgetCheckboxes.attr('disabled', false);
        },

        /**
         * Disable/enable gifts(products options)
         * @param status - disable/enable
         * @param mode - all/wc - without checkboxes (this.options.selectors.checkboxes)
         * @param container - container with options
         */
        toggleDisableItemsProductOptions: function (status, mode, container) {
            var ruleWrapperItems = this.options.counter.rule_wrapper.find(this.options.selectors.uncheckedItems),
                inputs = {},
                firstSelectOption = {},
                selectOptions = {},
                firstProductOptionSelector = '.product-options-wrapper .field:first-child';

            if (typeof(container) === 'object') {
                ruleWrapperItems = container;
            }

            inputs = this.getInputs(ruleWrapperItems, mode);
            selectOptions = ruleWrapperItems.find('select');

            if (status === 'disable') {
                inputs.attr('disabled', true);
                selectOptions.attr('disabled', true);

                return;
            }

            firstSelectOption = ruleWrapperItems.find(firstProductOptionSelector + ' select');

            if ($.trim(firstSelectOption.val())) {
                selectOptions.attr('disabled', false);
            } else {
                firstSelectOption.attr('disabled', false);
            }

            inputs.attr('disabled', false);
        },

        /**
         * Get input fields
         * @param container - jQuery - rule container
         * @param mode - all/wc - without checkboxes (this.options.selectors.checkboxes)
         */
        getInputs: function (container, mode) {
            if (mode === 'all') {
                return container.find('input');
            }

            return container.find('input').not(this.options.selectors.checkboxes);
        },

        /**
         * Change value of rule count
         * @param status - +/-
         */
        setRuleCount: function (status) {
            if (status === '+') {
                this.options.counter.count_wrapper.html(++this.options.counter.count);

                return;
            }

            this.options.counter.count_wrapper.html(--this.options.counter.count);
        },

        /**
         * Change value of total count. Enabled/disable submit button
         * @param status - +/-
         */
        setTotalCount: function (status) {
            var totalCountElement = $(this.options.selectors.total_count),
                totalCount = totalCountElement.attr('data-count');

            if (status === '+') {
                ++totalCount;
            } else {
                --totalCount;
            }

            if (totalCount > 0) {
                this.options.submitButton.attr('disabled', false);
            } else {
                this.options.submitButton.attr('disabled', true);
            }

            totalCountElement.attr('data-count', totalCount).text(totalCount);
        }
    });

    return $.digidirect.freeGiftByRules;
});
