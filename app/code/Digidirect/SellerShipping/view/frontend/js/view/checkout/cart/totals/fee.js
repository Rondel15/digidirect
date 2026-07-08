define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/totals'

], function (ko, Component, quote, priceUtils, totals) {
    'use strict';
    var show_hide_SellerShipping_blockConfig = window.checkoutConfig.show_hide_SellerShipping_block;
    var fee_label = window.checkoutConfig.fee_label;
    var custom_fee_amount = window.checkoutConfig.custom_fee_amount;
    var custom_in_fee_amount = window.checkoutConfig.custom_fee_amount_inc;
    var has_marketplacer_seller = window.checkoutConfig.quoteData.has_marketplacer_seller;

    return Component.extend({

        totals: quote.getTotals(),
        canVisibleSellerShippingBlock: show_hide_SellerShipping_blockConfig,
        getFormattedPrice: ko.observable(priceUtils.formatPrice(custom_fee_amount, quote.getPriceFormat())),
        getFeeLabel:ko.observable(fee_label),
        getInFeeLabel:ko.observable(window.checkoutConfig.inclTaxPostfix),
        getExFeeLabel:ko.observable(window.checkoutConfig.exclTaxPostfix),
        hasMarketplacerSeller: ko.observable(has_marketplacer_seller),

        isDisplayed: function () {
            return this.getValue() != 0;
        },
        isDisplayBoth: function () {
            return window.checkoutConfig.displayBoth;
        },
        displayExclTax: function () {
            return window.checkoutConfig.displayExclTax;
        },
        displayInclTax: function () {
            return window.checkoutConfig.displayInclTax;
        },
        isTaxEnabled: function () {
            return window.checkoutConfig.TaxEnabled;
        },
        getValue: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('fee')) {
                price = totals.getSegment('fee').value;
            }
            return price;
        },
        getInFormattedPrice: function() {
            var price = 0;
            if (this.totals() && totals.getSegment('fee')) {
                price = totals.getSegment('fee').value;
            }

            return priceUtils.formatPrice(price, quote.getPriceFormat());
        },
    });
});
