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
    };
});
