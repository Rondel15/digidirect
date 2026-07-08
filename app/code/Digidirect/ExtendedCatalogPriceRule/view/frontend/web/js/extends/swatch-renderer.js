/* global DOMParser */
define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/template',
    'text!Digidirect_ExtendedCatalogPriceRule/template/display_message.html',
    'priceUtils',
    'jquery/ui'
], function ($, _, $t, mageTemplate, messageTemplate, priceUtils) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            options: {
                extendedRulesBlock: '.block-extendedrule',
                extendedRulesTranslation: {
                    promotionTitle: $t('Promotion URL')
                }
            },
            _RenderControls: function () {
                this._super();
                
                this.parentResultbyRules = undefined;

                if (typeof this.options.jsonConfig.extended_rules !== 'undefined') {
                    this.parentResultbyRules = this.options.jsonConfig.extended_rules[this.options.jsonConfig.productId];
                    if (typeof this.parentResultbyRules !== 'undefined') {
                        this.renderExtendedRules(this, this.parentResultbyRules, this.options.jsonConfig.productId);
                    }
                }
            },
            _OnClick: function ($this, $widget, eventName) {
                this._super($this, $widget, eventName);

                var options = _.object(_.keys($widget.optionsMap), {}),
                    selectedIndex,
                    result;

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');

                    options[attributeId] = $(this).attr('option-selected');
                });

                selectedIndex = _.findKey($widget.options.jsonConfig.index, options);

                if (typeof $widget.options.jsonConfig.extended_rules !== 'undefined') {
                    result = $widget.options.jsonConfig.extended_rules[selectedIndex];
                }

                if (typeof result !== 'undefined') {
                    this.cleanExtendedRules($widget);
                    this.renderExtendedRules($widget, result, selectedIndex);
                } else {
                    this.cleanExtendedRules($widget);
                    if (typeof this.parentResultbyRules !== 'undefined') {
                        this.renderExtendedRules(this, this.parentResultbyRules, this.options.jsonConfig.productId);
                    }
                }
            },
            renderExtendedRules: function ($widget, result, selectedIndex) {
                _.each(result, function (rule) {
                    if (typeof rule.action_amount !== 'string') {
                        rule.action_amount = priceUtils.formatPrice(rule.action_amount, $widget.options.jsonConfig.priceFormat);
                    }
                    rule.pdp_description = this.decodeEscapedHtml(rule.pdp_description);

                    $(mageTemplate(messageTemplate, {
                        data: rule,
                        itemIndex: selectedIndex,
                        productId: $widget.options.jsonConfig.productId, // Parent ID
                        label: $widget.options.extendedRulesTranslation,
                        inProductList: $widget.inProductList
                    })).insertBefore($widget.element);
                }, this);
            },
            cleanExtendedRules: function ($widget) {
                $widget.element.prevAll(this.options.extendedRulesBlock).remove();
            },
            decodeEscapedHtml: function (encodedString) {
                var parser = new DOMParser(),
                    dom = parser.parseFromString(
                        '<!doctype html><body>' + this.replaceToTag(encodedString),
                        'text/html'
                    );

                return $(dom.body).html();
            },
            replaceToTag: function (string) {
                return string.replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>');
            }
        });

        return $.mage.SwatchRenderer;
    };
});
