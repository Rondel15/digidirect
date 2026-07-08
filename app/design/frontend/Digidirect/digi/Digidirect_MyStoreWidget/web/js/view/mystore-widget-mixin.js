/* eslint no-useless-escape: [0] */
define([
    'uiComponent',
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Customer/js/customer-data',
    'Digidirect_MyStoreWidget/js/model/store',
    'Digidirect_MyStoreWidget/js/action/set-store'
], function (Component, $, _, $t, customerData, store, setStore) {
    'use strict';

    var availabilityCheckArrayBox = window.availabilityCheckArray;
    var sendAdditionalAvailableInfo;
    var mixin = {
        defaults: {
            searchResultFormat: '%name',
            availabilityCheckArrayInfo: _.isObject(availabilityCheckArrayBox) && _.has(availabilityCheckArrayBox, 'content')
                ? availabilityCheckArrayBox.content
                : {}
        },

        initialize: function () {
            this._super();
            $(document).on('mystore.save.success', function (event, data) {
                if (_.isObject(data) && _.has(data, 'store')) {
                    this.updateStoreData(data.store);
                }
                this.updateAvailability(data);
            }.bind(this));
        },

        updateStoreData: function (data) {
            setStore({
                entityId: data.entity_id,
                entityName: data.entity_name,
                selectedStore: this.formatLabel(data) || data.name,
                storeUrl: data.url_key,
                isStoreSelected: data.length === 0 ? 0 : 1,
                openingHours: data.opening_hours,
                storeStreet: data.street,
                storePhoneNumber: data.phone_number,
                storeFaxNumber: data.fax,
                storeEmail: data.email
            });
            this.updateAvailability(data);
        },

        updateAvailability: function (data) {
            if (_.isEmpty(this.availabilityCheckArrayInfo)) {
                return;
            }

            for (var key in this.availabilityCheckArrayInfo) {
                if (data.name === key) {
                    if (this.availabilityCheckArrayInfo[key] === "1") {
                        sendAdditionalAvailableInfo = 'Available';
                        $('#availabilityAddition').text(sendAdditionalAvailableInfo).css('color', '#76a93f');
                    } else {
                        sendAdditionalAvailableInfo = 'Unavailable';
                        $('#availabilityAddition').text(sendAdditionalAvailableInfo).css('color', '#777');
                    }
                }
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
