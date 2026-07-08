define([
    'Digidirect_Locator/js/model/locations'
], function (locations) {
    'use strict';

    return function (items, settings) {
        locations.items(items);
        locations.settings(settings);
        locations.pager([]);
        locations.currentPage(1);
    };
});
