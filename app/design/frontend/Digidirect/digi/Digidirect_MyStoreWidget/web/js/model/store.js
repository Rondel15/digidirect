define(['ko'], function (ko) {
    'use strict';

    return {
        entityId: ko.observable(),
        entityName: ko.observable(),
        selectedStore: ko.observable(),
        storeUrl: ko.observable(),
        isStoreSelected: ko.observable(0),
        openingHours: ko.observable(),
        storeStreet: ko.observable(),
        storeUrlKey: ko.observable(),
        storePhoneNumber: ko.observable(),
        storeFaxNumber: ko.observable(),
        storeEmail: ko.observable()
    };
});
