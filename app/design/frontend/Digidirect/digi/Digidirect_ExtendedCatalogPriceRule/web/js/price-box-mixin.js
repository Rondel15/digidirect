define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'underscore',
    'mage/template',
    'jquery/ui'
], function ($, utils, _, mageTemplate) {
    'use strict';

    return function (target) {
        $.widget('mage.priceBox', target, {
            reloadPrice: function reDrawPrices() {
                this._super();

                var priceFormat = (this.options.priceConfig && this.options.priceConfig.priceFormat) || {},
                    priceTemplate = mageTemplate(this.options.priceTemplate);

                _.each(this.cache.displayPrices, function (price, priceCode) {
                    var $afterCashback = $('[data-price-type="afterCashback"]', this.element),
                        afterCashbackVal;

                    price.final = _.reduce(price.adjustments, function (memo, amount) {
                        return memo + amount;
                    }, price.amount);

                    if (priceCode === 'finalPrice' && $('[data-price-type="' + priceCode + '"]', this.element).length && $afterCashback.length) {
                        afterCashbackVal = price.final - $afterCashback.data('amount');
                        price.formatted = utils.formatPrice(afterCashbackVal, priceFormat);
                        $afterCashback.html(priceTemplate({
                            data: price
                        }));
                    }
                }, this);
            }
        });

        return $.mage.priceBox;
    };
});