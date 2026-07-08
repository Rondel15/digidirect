define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Digidirect_Collect/js/model/shipping-address/form-popup-state',
    'Digidirect_Collect/js/model/collect',
    'Digidirect_Collect/js/action/set-collect-places'
], function ($, _, ko, Component, $t, modal, formPopUpState, collectPlaces, setCollectPlaces) {
    'use strict';
    var popUp = null,
        isCollectEnableOnCheckout = window.checkoutConfig.quoteData.is_collect_enable_on_checkout,
        isSingleCartCollectVariation = window.checkoutConfig.quoteData.is_single_cart_collect_variation,
        quoteCollectPlaces = window.checkoutConfig.quoteData.collect_places,
        changePlaceUrl = window.checkoutConfig.quoteData.change_place_url,
        placesUrl = window.checkoutConfig.quoteData.get_places_url,
        collectDeliverInsteadUrl = window.checkoutConfig.quoteData.deliver_instead_url,
        distanceList = window.checkoutConfig.quoteData.distance_list;

    return Component.extend({
        defaults: {
            collectFormTemplate: 'Digidirect_Collect/checkout/shipping-address/form'
        },
        collectPlaces: collectPlaces.places,
        collectPlaceRows: ko.observableArray([]),
        isVisibleMoreButton: ko.observable(false),
        initialize: function () {
            var self = this;

            setCollectPlaces();

            this._super();

            this.isFormPopUpVisible.subscribe(function (value, second) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },
        formItemId: '',
        isFormPopUpVisible: formPopUpState.isVisible,
        isCollectEnableOnCheckout: ko.observable(isCollectEnableOnCheckout || false),
        isSingleCartCollectVariation: ko.observable(isSingleCartCollectVariation || false),
        changePlaceUrl: changePlaceUrl,
        placesUrl: placesUrl,
        presetCollectPlace: ko.observableArray([]),
        distanceList: distanceList,
        getPopUp: function () {
            var self = this,
                buttons = this.popUpForm.options.buttons;

            $('#collect_quote_item_id').val(this.formItemId);

            if (!popUp) {
                this.popUpForm.options.buttons = [
                    {
                        text: buttons.search.text ? buttons.search.text : $t('Search'),
                        class: buttons.search.class ? buttons.search.class : 'action secondary',
                        click: self.searchCollectPlaces.bind(self)
                    },
                    {
                        text: buttons.save.text ? buttons.save.text : $t('Buy and Collect'),
                        class: buttons.save.class ? buttons.save.class : 'action primary',
                        click: self.submitCollect.bind(self)
                    }
                ];
                this.popUpForm.options.closed = function () {
                    self.isFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                popUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return popUp;
        },
        onClosePopUp: function () {
            this.getPopUp().closeModal();
        },
        showFormPopUp: function (id) {
            this.formItemId = id.item_id;
            if (quoteCollectPlaces && quoteCollectPlaces[this.formItemId]) {
                this.presetCollectPlace.removeAll();
                this.presetCollectPlace.push(this.collectPlaces()[this.findCollectPlace(this.formItemId)]);
            }
            this.isFormPopUpVisible(true);
        },
        getDeliveryPlace: function (item) {
            if (quoteCollectPlaces && quoteCollectPlaces[item.item_id]) {
                return true;
            }
            return false;
        },
        setDeliveryInstead: function (quoteItemId) {
            var data = {};
            if (quoteItemId) {
                data.quote_item_id = quoteItemId;
            }

            $.ajax({
                url: collectDeliverInsteadUrl,
                data: data,
                success: function (data) {
                    if (data.result) {
                        console.log('DeliverInstead true');
                    } else {
                        console.log('DeliverInstead false');
                    }
                }
            });
        },
        submitCollect: function () {
            var url = $(this.popUpForm.element).attr('data-action'),
                self = this,
                collectPlaceId = $('input[name=collect_place_id]:checked', this.popUpForm.element).val(),
                collectPlaceStorageName = $('input[name=collect_place_id]:checked', this.popUpForm.element).attr('data-collectplace-storage-name');

            $.ajax({
                url: url,
                data: {
                    quote_item_id: self.formItemId,
                    collect_place_id: collectPlaceId,
                    collect_place_storage_name: collectPlaceStorageName
                },
                success: function (data) {
                    if (data.data) {
                        self.onClosePopUp();
                        self.collectPlaceRows.removeAll();
                        self.updateCollectPlaces(self.formItemId, data.data);
                    } else {
                        console.log('false');
                    }
                }
            });
        },
        searchCollectPlaces: function () {
            var self = this,
                collectQty = 1;

            _.each(window.checkoutConfig.quoteItemData, function (item) {
                if (self.formItemId === item.item_id) {
                    collectQty = item.qty;
                    return true;
                }
            });

            $.ajax({
                url: placesUrl,
                data: {
                    collect_postcode: $('#collect_postcode').val(),
                    collect_distance: $('#collect_distance').val(),
                    collect_qty: collectQty,
                    quote_item_id: $('#collect_quote_item_id').val()
                },
                success: function (result) {
                    self.collectPlaceRows.removeAll();
                    self.savedCollectPlaces = result.data;
                    self.addCollectPlaces();
                }
            });
        },
        updateCollectPlaces: function (id, data) {
            var places = ko.toJS(this.collectPlaces()),
                index = this.findCollectPlace(id);

            this.collectPlaces.replace(this.collectPlaces()[index], ko.observable(_.extend(places[index], data))());
        },
        findCollectPlace: function (id) {
            return _.findIndex(this.collectPlaces(), function (item) {
                return item.item_id == id;
            });
        },
        updateStorageName: function (id) {
            $('#collect_place_storage_name').val(id);
            return true;
        },
        addCollectPlaces: function () {
            var places = this.savedCollectPlaces.slice(0);
            _.each(this.savedCollectPlaces, function (item, i) {
                if (this.popUpForm.options.placesOnPage) {
                    if (i < this.popUpForm.options.placesOnPage) {
                        places.shift();
                    } else {
                        return;
                    }
                }
                this.collectPlaceRows.push(item);
            }, this);
            if (this.popUpForm.options.placesOnPage) {
                this.isVisibleMoreButton(!!places.length);
                this.savedCollectPlaces = places;
            }
        }
    });
});
