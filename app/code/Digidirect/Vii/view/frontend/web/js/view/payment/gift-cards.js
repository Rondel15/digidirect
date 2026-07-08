define([
    'underscore',
    'ko',
    'Magento_GiftCardAccount/js/view/summary/gift-card-account',
    'Magento_Checkout/js/model/quote',
    'Magento_GiftCardAccount/js/action/remove-gift-card-from-quote'
], function (_, ko, Component, quote, removeAction) {
    'use strict';

    return Component.extend({
        cardsData: ko.observableArray([]),

        initialize: function () {
            this._super();
            this.bindSubscribe();
        },

        /**
         * Bind subscribe totals change
         */
        bindSubscribe: function () {
            quote.totals.subscribe(function (newValue) {
                this.checkActionType();
            }, this);
        },

        /**
         * Check type of action
         */
        checkActionType: function () {
            var data = this.getDataByCode(),
                cardsData;

            if (data && data.extension_attributes) {
                cardsData = JSON.parse(data.extension_attributes.gift_cards);
                if (cardsData.length > this.cardsData().length) {
                    this.addCardItems(cardsData);
                } else {
                    this.removeCardItem(cardsData);
                }
            } else {
                if (this.cardsData().length) {
                    this.removeAllCards();
                }
            }
        },

        /**
         * Add card items
         * @param {array} cardsData
         */
        addCardItems: function (cardsData) {
            _.each(cardsData, function (card) {
                this.addCardData(card);
            }, this);
        },

        /**
         * Remove card item from data
         * @param {array} cardsData
         */
        removeCardItem: function (cardsData) {
            var cardNumbers = _.map(cardsData, function (item) {
                    return item.c;
                }),
                removedItem = _.filter(this.cardsData(), function (card) {
                    return !_.contains(cardNumbers, card.cardNumber);
                }, this)[0];
            this.cardsData.remove(function (card) {
                return card.cardNumber === removedItem.cardNumber;
            });
        },

        /**
         * Remove all cards from data
         */
        removeAllCards: function () {          
            this.cardsData.removeAll();
        },

        /**
         * Add card to data
         * @param {object} card
         */
        addCardData: function (card) {
            if (!this.isExistCard(card)) {
                this.cardsData.push({
                    cardNumber: card.c,
                    cardId: 'vii-card-' + card.c,
                    pinId: 'vii-card-pin-' + card.c
                });
            }
        },

        /**
         * Check existing of card
         * @param {object} card
         */
        isExistCard: function (card) {
            return _.where(this.cardsData(), {cardNumber: card.c}).length;
        },

        /**
         * Check existing saved data
         */
        isHasCards: function () {
            return this.cardsData().length;
        },

        /**
         * Get data from quote
         * @returns {*}
         */
        getDataByCode: function () {
            return _.where(quote.totals().total_segments, {code: this.code})[0];
        },

        /**
         * Remove card from quote
         * @param {object} card
         * @param {jquery object} e
         */
        removeGiftCard: function (card, e) {
            e.preventDefault();
            if (card.cardNumber) {
                removeAction(card.cardNumber);
            }
        }
    });
});
