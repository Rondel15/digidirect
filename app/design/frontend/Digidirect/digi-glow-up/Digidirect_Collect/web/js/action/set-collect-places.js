define([
    'underscore',
    'ko',
    'Digidirect_Collect/js/model/collect'
], function (_, ko, collect) {
    'use strict';

    var quoteCollectPlaces = window.checkoutConfig.quoteData.collect_places;

    return function () {
        _.each(quoteCollectPlaces, function (place) {
            collect.places.push(place);
        });
    };
});
