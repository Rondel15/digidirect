define(['ko'], function (ko) {
    'use strict';

    return {
        entityId: ko.observable(),
        entityName: ko.observable(),
        selectedStore: ko.observable(),
        storeUrl: ko.observable(),
        isStoreSelected: ko.observable(0)
    };
});
