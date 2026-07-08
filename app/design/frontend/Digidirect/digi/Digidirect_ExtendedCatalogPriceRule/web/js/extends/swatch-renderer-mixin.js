define([
    'jquery',
    'underscore',
    'mage/translate',
    'mage/template',
    'text!Digidirect_ExtendedCatalogPriceRule/template/display_message.html',
    'priceUtils',
    'catalogPriceRuleModal',
    'Digidirect_ExtendedCatalogPriceRule/js/extends/swatch-renderer',
    'jquery/ui'
], function ($, _, $t, mageTemplate, messageTemplate, priceUtils, catalogPriceRuleModal) {
    'use strict';

    return function (target) {
        $.widget('mage.SwatchRenderer', target, {
            options: {
                extendedRulesTranslation: {
                    defaultRuleLabel: $t('Special Offer'),
                    afterCashback: $t('After Cashback'),
                    beforeCashback: $t('Before Cashback')
                }
            },
            _OnChange: function ($this, $widget) {
                this._super($this, $widget);

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
                        var currentOptionPrice = this.getCurrentOptionPrice($widget.options.jsonConfig, rule.product_id);

                        if (currentOptionPrice) {
                            rule.before_cashback_price = priceUtils.formatPrice(+currentOptionPrice.finalPrice.amount, $widget.options.jsonConfig.priceFormat);
                            rule.after_cashback_price = priceUtils.formatPrice((+currentOptionPrice.finalPrice.amount - rule.action_amount), $widget.options.jsonConfig.priceFormat);
                        } else {
                            console.error('Can\'t find current option price');
                            rule.before_cashback_price = 0;
                            rule.after_cashback_price = 0;
                        }
                    }
                    if (rule.pdp_description) {
                        rule.pdp_description = this.decodeEscapedHtml(rule.pdp_description);
                    }
                    
                    console.log("TESTAFDF");

                    var $promotionLabel,
                        $promotionHtml = $(mageTemplate(messageTemplate, {
                        data: rule,
                        itemIndex: selectedIndex,
                        productId: $widget.options.jsonConfig.productId, // Parent ID
                        label: $widget.options.extendedRulesTranslation,
                        inProductList: $widget.inProductList
                    }));

                    if ($widget.inProductList) {
                        $promotionHtml.insertBefore($widget.element.parents(this.options.selectorProductTile).find(this.options.selectorProductPrice));
                        $promotionLabel = $promotionHtml.find('[data-rule-label]');
                        if ($promotionLabel.length) {
                            $promotionLabel.catalogPriceRuleModal({'ruleId': $promotionLabel.data('rule-label')});
                        }
                    } else {
                        $promotionHtml.insertBefore($('.product-info-main .product-info-price'));
                    }

                }, this);
            },
            cleanExtendedRules: function ($widget) {
                if ($widget.inProductList) {
                    $widget.element.parents(this.options.selectorProductTile).find(this.options.selectorProductPrice).prevAll(this.options.extendedRulesBlock).remove();
                } else {
                    $('.product-info-main .product-info-price').prevAll(this.options.extendedRulesBlock).remove();
                }
            },
            getCurrentOptionPrice: function (jsonConfig, productId) {
                if (!jsonConfig || !jsonConfig.optionPrices || !productId) {
                    return false;
                }
                return jsonConfig.optionPrices[productId];
            }
        });

        return $.mage.SwatchRenderer;
    };
});
