define([
    'Digidirect_MyStoreWidget/js/model/store'
], function (store) {
    'use strict';

    return function (item) {
        store.entityId(item.entityId);
        store.entityName(item.entityName);
        store.selectedStore(item.selectedStore);
        store.storeUrl(item.storeUrl);
        store.isStoreSelected(item.isStoreSelected);
        store.openingHours(item.openingHours);
        store.storeStreet(item.storeStreet);
        store.storeUrlKey(item.storeUrlKey);
        store.storePhoneNumber(item.storePhoneNumber);
        store.storeFaxNumber(item.storeFaxNumber);
        store.storeEmail(item.storeEmail);
    };
});
