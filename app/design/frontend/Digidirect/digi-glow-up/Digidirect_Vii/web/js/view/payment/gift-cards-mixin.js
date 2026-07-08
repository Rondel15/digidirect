define([
    'underscore'
], function (
    _
) {
    'use strict';
    var mixin = {
        removeCardItem: function (cardsData) {
            var cardNumbers = _.map(cardsData, function (item) {
                    return item.c;
                }),
                removedItem = _.filter(this.cardsData(), function (card) {
                    return !_.contains(cardNumbers, card.cardNumber);
                }, this)[0];
            this.cardsData.remove(function (card) {
                if (removedItem) {
                    return card.cardNumber === removedItem.cardNumber;
                } else {
                    return false;
                }
            });
        }
    };
    return function (target) {
        return target.extend(mixin);
    };
});
