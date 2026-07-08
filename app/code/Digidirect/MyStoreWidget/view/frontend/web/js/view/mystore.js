define([
    'uiComponent',
    'jquery',
    'underscore',
    'ko',
    'Digidirect_MyStoreWidget/js/model/messages',
    'Magento_Checkout/js/model/error-processor',
    'Digidirect_MyStoreWidget/js/model/store',
    'Digidirect_MyStoreWidget/js/action/set-store'
], function (Component, $, _, ko, messageContainer, errorProcessor, store, setStore) {
    'use strict';

    var config = window.checkoutConfig.myStoreWidget;

    return Component.extend({
        defaults: {
            template: 'Digidirect_MyStoreWidget/checkout/mystore',
            selectedSelector: '-selected'
        },
        isVisible: ko.observable(true),
        isLoading: ko.observable(false),
        store: store,
        myStoreConfig: config,
        initialize: function () {
            this._super();

            if (this.myStoreConfig === undefined) {
                this.isVisible(false);
            } else {
                setStore({
                    entityId: config.abstract_entity_id,
                    selectedStore: config.selected_store,
                    storeUrl: config.store_url,
                    isStoreSelected: config.is_selected_store
                });
                this.attachListeners();
            }

            return this;
        },
        attachListeners: function () {
            var self = this;
            $(document).on('mystore.save.send', function () {
                self.isLoading(true);
            });
            $(document).on('mystore.save.success', function (event, data) {
                if (data.error) {
                    messageContainer.addErrorMessage({
                        'message': data.message
                    });
                } else {
                    setStore({
                        entityId: data.store.entity_id,
                        selectedStore: data.store.formatLabel ? data.store.formatLabel : data.store.name,
                        storeUrl: data.store.url,
                        isStoreSelected: 1
                    });

                    messageContainer.addSuccessMessage({
                        'message': data.message
                    });
                }
                self.isLoading(false);
            });
            $(document).on('mystore.save.error', function (response) {
                errorProcessor.process(response, messageContainer);
                self.isLoading(false);
            });
        },
        getMyStoreOptions: function () {
            return $.extend(true, {}, this.getPrepareOptions(this.myStoreConfig), this.widget.options);
        },
        getPrepareOptions: function (config) {
            var options = {
                    searchUrl: config.search_url,
                    saveUrl: config.save_url,
                    autocompleteSettings: {
                        minLength: config.min_length
                    },
                    isSelectedStore: config.is_selected_store
                },
                countCountries = config.available_countries.length;
            if (config.is_geo_location_enabled) {
                options.isNeedKeepGeoLocation = config.is_need_keep_geo_location;
                options.isGeoLocationEnabled = config.is_geo_location_enabled;
                options.storesList = config.stores_list;
            }
            if (config.is_google_auto_suggest_enabled) {
                options.isGoogleAutoSuggestEnabled = config.is_google_auto_suggest_enabled;
                if (countCountries === 1) {
                    options.singleCountryData = {'country': [config.single_country_data]};
                } else if (countCountries > 1) {
                    options.singleCountryData = {'country': config.single_country_data};
                }
            }

            return options;
        },
        getEntityName: function () {
            return config.entity_name;
        },
        getSaveUrl: function () {
            return config.save_url;
        },
        isStoreSelectedClassName: function () {
            return config.is_selected_store ? this.selectedSelector : '';
        },
        isGoogleAutoSuggestEnabled: function () {
            return config.is_google_auto_suggest_enabled;
        },
        getFormKey: function () {
            return window.checkoutConfig.formKey;
        }
    });
});
