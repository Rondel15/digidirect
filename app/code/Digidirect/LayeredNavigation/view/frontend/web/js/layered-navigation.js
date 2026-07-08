define([
    'jquery',
    'underscore',
    'Digidirect_LayeredNavigation/js/dist/common/component',
    'jquery/ui',
    'productListToolbarForm'
], function ($, _, Component) {
    'use strict';

    var config = {};

    $.widget('mage.layeredNavigation', {
        options: {
            triggerApplyButton: false,
            triggerApplyMode: false,
            applyModeBreakpoint: '(max-width: 767px)',
            enableAjax: false,
            enabledSeoUrls: false,
            postfix: '',
            baseUrl: '',
            selectors: {
                applyButton: '.ln-apply',
                allSwatches: '.swatch-option-link-layered',
                selectedSwatches: '.swatch-option-link-layered.selected',
                allLinks: '.filter-link',
                selectedLinks: '.filter-link.selected',
                filterContent: '.filter-options-content',
                blockFilters: '.block.filter'
            },
            enableFilterRemember: false,
            filterRememberDataAttr: '[data-role="filter-remember"]',
            viewCustom: '',
            addOn: ''
        },
        
        _create: function () {
            config = this.options;
            new Component(config);
        }
    });

    /**
     * Default filter
     */
    $.widget('mage.layeredNavigationFilterItemDefault', $.mage.layeredNavigation, {
        options: {},
        _create: function () {
            this.options.element = this.element;
            _.extend(this.options, config);
            Component.loadFilter('default', this.options);
        }
    });

    /**
     * Drop-down filter
     */
    $.widget('mage.layeredNavigationFilterDropdown', $.mage.layeredNavigation, {
        options: {},
        _create: function () {
            this.options.dropdownElement = this.element;
            _.extend(this.options, config);
            Component.loadFilter('dropdown', this.options);
        }
    });

    /**
     * Link filter
     */
    $.widget('mage.layeredNavigationFilterLink', $.mage.layeredNavigation, {
        options: {
            resetParameters: 0
        },
        _create: function () {
            this.options.linkElement = this.element;
            _.extend(this.options, config);
            Component.loadFilter('link', this.options);
        }
    });

    /**
     * Slider filter
     */
    $.widget('mage.layeredNavigationFilterSlider', $.mage.layeredNavigation, {
        options: {},
        _create: function () {
            this.options.sliderElement = this.element;
            _.extend(this.options, config);
            Component.loadFilter('slider', this.options);
        }
    });
});
