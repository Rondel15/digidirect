/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/totals',
    'uiComponent',
    'Magento_Checkout/js/model/step-navigator',
    'Magento_Checkout/js/model/quote'
], function (ko, $, totals, Component, stepNavigator, quote) {
    'use strict';

    var useQty = window.checkoutConfig.useQty;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/cart-items'
        },
        totals: totals.totals(),
        items: ko.observable([]),
        maxCartItemsToDisplay: window.checkoutConfig.maxCartItemsToDisplay,
        cartUrl: window.checkoutConfig.cartUrl,

        /**
         * @deprecated Please use observable property (this.items())
         */
        getItems: totals.getItems(),

        /**
         * Returns cart items qty
         *
         * @returns {Number}
         */
        getItemsQty: function () {
            return parseFloat(this.totals['items_qty']);
        },

        /**
         * Returns count of cart line items
         *
         * @returns {Number}
         */
        getCartLineItemsCount: function () {
            return parseInt(totals.getItems()().length, 10);
        },

        /**
         * Returns shopping cart items summary (includes config settings)
         *
         * @returns {Number}
         */
        getCartSummaryItemsCount: function () {
            return useQty ? this.getItemsQty() : this.getCartLineItemsCount();
        },
        
        getSellers: function () {
            return window.checkoutConfig.quoteData.marketplacer_sellers;
        },

        /**
         * @inheritdoc
         */
        initialize: function () {
            this._super();
            console.log("Override cart-items.js!");
            
            // Set initial items to observable field
            /*_.each(window.checkoutConfig.quoteData.marketplacer_sellers, function (seller) {
                console.log(seller);
            });*/
            
            this.setItems(totals.getItems()(), window.checkoutConfig.quoteData.marketplacer_sellers);

            // Subscribe for items data changes and refresh items in view
            totals.getItems().subscribe(function (items) {
                this.setItems(items);
            }.bind(this));
            
            /*console.log("JS for seller!");
            var elements = document.getElementsByClassName("seller-name");
            var elementsArr = Array.from(elements);
            console.log("elementsArr: " + JSON.stringify(elementsArr));
            elementsArr.forEach(myFunction);

            function myFunction(currentValue, index) {
                console.log(currentValue.innerHTML);
            }*/
            
        },

        /**
         * Set items to observable field
         *
         * @param {Object} items
         */
        setItems: function (items, sellers) {
            if (items && items.length > 0) {
                items = items.slice(parseInt(-this.maxCartItemsToDisplay, 10));
            }
            
            this.items(items);
        },

        /**
         * Returns bool value for items block state (expanded or not)
         *
         * @returns {*|Boolean}
         */
        isItemsBlockExpanded: function () {
            return quote.isVirtual() || stepNavigator.isProcessed('shipping');
        }
    });
});
