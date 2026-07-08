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

    return Component.extend({
        defaults: {
            container: '#block-mystorebar',
            searchResultFormat: '%name (%state, %postcode)'
        },
        store: store,
        initialize: function () {
            this._super();
            this.myStore = customerData.get('mystorewidget');

            if (this.myStore().currentStoreData && !Array.isArray(this.myStore().currentStoreData)) {
                this.updateStoreData(this.myStore().currentStoreData);
            } else {
                this.myStore.subscribe(function (data) {
                    this.updateStoreData(data.currentStoreData);
                }.bind(this));
            }
        },
        updateStoreData: function (data) {
            setStore({
                entityId: data.entity_id,
                entityName: data.entity_name,
                selectedStore: this.formatLabel(data) || data.name,
                storeUrl: data.url_key,
                isStoreSelected: data.length === 0 ? 0 : 1
            });
        },
        formatLabel: function (item) {
            var format = this.searchResultFormat,
                attributesArray,
                result = item.name;
            if (format) {
                attributesArray = format.match(/[a-z1-9\_\-]+/ig);
                if (attributesArray.length) {
                    $.each(attributesArray, function (key, value) {
                        var val = item[value];
                        if (val !== undefined) {
                            format = format.replace('%' + value, val);
                        }
                    });
                    format = this.formatLabelPostProcess(format);
                    return format;
                }
            }
            result = this.formatLabelPostProcess(result);
            return result;
        },
        formatLabelPostProcess: function (str) {
            str = str.replace(/\S+\%\S+/ig, '');
            str = $.trim(str.replace(/\%\S+/ig, ''));
            return str;
        }
    });
});
