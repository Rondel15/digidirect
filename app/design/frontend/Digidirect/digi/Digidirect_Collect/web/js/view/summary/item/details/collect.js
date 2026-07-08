define([
    'underscore',
    'ko',
    'uiComponent',
    'Digidirect_Collect/js/model/collect'
], function (_, ko, Component, collectPlaces) {
    'use strict';

    var isSingleCollectVariation = window.checkoutConfig.quoteData.is_single_collect_variation,
        isSingleCartCollectVariation = window.checkoutConfig.quoteData.is_single_cart_collect_variation,
        quoteCollectPlaces = window.checkoutConfig.quoteData.collect_places,
        quoteItemData = window.checkoutConfig.quoteItemData;

    return Component.extend({
        defaults: {
            displayArea: 'after_details',
            template: 'Digidirect_Collect/summary/item/details/collect'
        },
        isSingleCollectVariation: ko.observable(isSingleCollectVariation || false),
        isSingleCartCollectVariation: ko.observable(isSingleCartCollectVariation || false),
        collectPlaces: collectPlaces.places,
        quoteItemData: quoteItemData,
        getDeliveryPlace: function (item) {
            return ko.computed(function () {
                if (quoteCollectPlaces && quoteCollectPlaces[item.item_id]) {
                    var index = _.findIndex(this.collectPlaces(), function (place) {
                        return place.item_id == item.item_id;
                    });
                    return this.collectPlaces()[index].collect_place_name;
                }
                return false;
            }.bind(this));
        },
        getIsVirtual: function (item) {
            var isVirtual = false;
            _.each(this.quoteItemData, function (quoteItem) {
                if (+quoteItem.item_id === +item.item_id) {
                    isVirtual = +quoteItem.is_virtual;
                }
            });
            return isVirtual;
        }
    });
});
