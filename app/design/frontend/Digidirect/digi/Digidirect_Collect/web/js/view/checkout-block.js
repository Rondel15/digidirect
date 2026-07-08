define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'Digidirect_Collect/js/model/shipping-address/single-cart-form-popup-state',
    'Digidirect_Collect/js/model/block',
    'Digidirect_Collect/js/action/set-block-places',
    'Magento_Checkout/js/model/url-builder',
    'Digidirect_Locator/js/model/locations',
    'mage/storage',
    'Magento_Checkout/js/model/quote',
    'Magento_Ui/js/lib/core/events',
], function ($, _, ko, Component, $t, modal, formPopUpState, collectPlaces, setBlockPlaces, urlBuilder, locations, storage, quote, events) {
    'use strict';

    var singleCartPopUp = null,
        isCollectEnableOnCheckout = window.checkoutConfig.quoteData.is_collect_enable_on_checkout,
        isSingleCartCollectVariation = window.checkoutConfig.quoteData.is_single_cart_collect_variation,
        quoteCollectPlaces = window.checkoutConfig.quoteData.collect_places,
        placesUrl = window.checkoutConfig.quoteData.get_places_url,
        distanceList = window.checkoutConfig.quoteData.distance_list,
        selectedStore = window.checkoutConfig.quoteData.selected_collect_place;

    return Component.extend({
        defaults: {
            collectBlock: '[data-role="collect-block"]',
            visibleClass: '-visible',
            collectFormTemplate: 'Digidirect_Collect/checkout/shipping-address/block-form',
            entityName: 'abstract_entity_store'
        },
        collectPlaces: collectPlaces.places,
        isCollectSelected: ko.observable(false),
        collectPlaceRows: ko.observableArray([]),
        isInProgress: false,
        isPaginationEnable: true,
        defaultPerPage: 10,
        pageFrame: 5,
        pageJump: 2,
        locations: locations,
        modules: {
            details: 'locator_details'
        },
        initialize: function () {
            setBlockPlaces();
            
            window.selectStore = ko.observable(false);

            this._super();

            this.setPreselectedStore();
            this.checkIsCollectSelected();
            this.onSubscribe();
            this.setPlacesToQuote();
            this.renderItems();
        },
        formItemId: '',
        isSingleCartFormPopUpVisible: formPopUpState.isVisible,
        isCollectEnableOnCheckout: ko.observable(isCollectEnableOnCheckout || false),
        isSingleCartCollectVariation: ko.observable(isSingleCartCollectVariation || false),
        placesUrl: placesUrl,
        distanceList: distanceList,
        checkIsCollectSelected: function () {
            if (this.collectPlaces().length > 0) {
                this.isCollectSelected(true);
                quote.isCollectSelected = true;
            } else {
                this.isCollectSelected(false);
                quote.isCollectSelected = false;
            }
        },
        renderItems: function () {
            var self = this;
            
            if (this.isPaginationEnable) {
                this.pageFrame--;
                this.paginationObservable();
            } else {
                this.perPage = function () {
                    return this.defaultPerPage;
                };
                this.locationList = locations.items;
            }
        },
        onSubscribe: function () {
            var self = this;
            this.isSingleCartFormPopUpVisible.subscribe(function (value) {
                if (value) {
                    self.getPopUp().openModal();
                }
            });
        },
        getPopUp: function () {
            var self = this;

            $('#collect_quote_item_id').val(this.formItemId);

            if (!singleCartPopUp) { 
                this.popUpForm.options.buttons = [];
                this.popUpForm.options.closed = function () {
                    self.isSingleCartFormPopUpVisible(false);
                };

                this.popUpForm.options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
                this.popUpForm.options.keyEventHandlers = {
                    escapeKey: this.onClosePopUp.bind(this)
                };

                singleCartPopUp = modal(this.popUpForm.options, $(this.popUpForm.element));
            }

            return singleCartPopUp;
        },
        onClosePopUp: function () {
            if (this.isSingleCartFormPopUpVisible()) {
                this.getPopUp().closeModal();
            }
        },
        showFormPopUp: function (data, e) {
            if (this.collectPlaces().length > 0) {
                this.formItemId = this.collectPlaces()[0].item_id;
            }
            this.isSingleCartFormPopUpVisible(true);
        },
        getDeliveryPlace: function (item) {
            if (item) {
                if (quoteCollectPlaces && quoteCollectPlaces[item.item_id]) {
                    return true;
                }
            }
            return false;
        },
        submitCollect: function () {
            var collectPlaceId = $('input[name=collect_place_id]:checked', this.popUpForm.element).val(),
                collectPlaceStorageName = $('input[name=collect_place_id]:checked', this.popUpForm.element).attr('data-collectplace-storage-name');

            this.applyCollectPlaceToAllItems(collectPlaceId, collectPlaceStorageName);
        },
        searchCollectPlaces: function (element) {
            var self = this,
                collectQty = 1,
                $form = $(element).closest('form');

            if ($form.length) {
                if (!$form.valid()) {
                    return;
                }
            }

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
                    _.each(result.data, function (item) {
                        self.collectPlaceRows.push(item);
                    });
                }
            });
        },
        updateCollectPlaces: function (data) {
            this.collectPlaces.unshift(data[0]);
        },
        updateStorageName: function (id) {
            $('#collect_place_storage_name').val(id);

            return true;
        },
        toggleCollectType: function (data, e) {
            var method = $(e.currentTarget).val();
            $(this.collectBlock).removeClass(this.visibleClass);
            $('[data-collect-type="' + method + '"]').addClass(this.visibleClass);

            if (method === 'delivery') {
                this.applyDeliveryToAllItems();
                this.isCollectSelected(false);
                quote.isCollectSelected = false;
            } else {
                this.isCollectSelected(true);
                quote.isCollectSelected = true;
                
                this.showFormPopUp();
            }
        },
        applyDeliveryToAllItems: function () {
            var self = this,
                serviceUrl = urlBuilder.createUrl('/collectplace/delivery/all', {});

            storage.post(serviceUrl).done(function (response) {
                self.onSuccessDelivery(response);
            }).fail(function (response) {
                self.onErrorDelivery(response);
            });
        },
        onSuccessDelivery: function (response) {
            this.collectPlaces.removeAll();
        },
        onErrorDelivery: function (response) {},
        applyCollectPlaceToAllItems: function (id, name) {
            console.log("applyCollectPlaceToAllItems : " + id + ", " + name);
            var self,
                serviceUrl,
                payload;

            if (!this.isInProgress) {
                this.isInProgress = true;
                self = this;
                serviceUrl = urlBuilder.createUrl('/collectplace/apply/all', {});
                payload = {
                    collectPlaceId: id,
                    storageName: name
                };

                storage.post(serviceUrl, JSON.stringify(payload)).done(function (response) {
                    self.onSuccessApplyPlace(response);
                    self.isInProgress = false;
                }).fail(function (response) {
                    self.onErrorApplyPlace(response);
                    self.isInProgress = false;
                });
            }
        },
        onSuccessApplyPlace: function (response) {
            this.onClosePopUp();
            this.updateCollectPlaces(JSON.parse(response));
            this.collectPlaceRows.removeAll();
            this.setPlacesToQuote();
        },
        onErrorApplyPlace: function (response) {},
        setPlacesToQuote: function () {
            quote.collectPlaces = this.collectPlaces();
        },
        setPreselectedStore: function () {
            if (selectedStore) {
                return this.isCollectSelected.subscribe(function (isCollect) {
                    if (isCollect && this.collectPlaces().length === 0) {
                        this.applyCollectPlaceToAllItems(selectedStore.entity_id, this.entityName);
                    }
                }.bind(this));
            }
            return null;
        },
        paginationObservable: function () {
            var self = this;

            self.frameStart = ko.observable(1);
            self.frameEnd = ko.observable(1 + this.pageFrame);

            self.perPage = ko.computed(function () {
                if (locations.settings() !== undefined && locations.settings().stores_on_locator_page) {
                    return locations.settings().stores_on_locator_page;
                }
                return self.defaultPerPage;
            });

            self.locationList = ko.computed(function () {
                var startIndex = self.currentPage() === 1 ? 0 : (self.currentPage() - 1) * self.perPage();
                return locations.items().slice(startIndex, startIndex + self.perPage());
            });

            self.totalItemCount = ko.computed(function () {
                return locations.items().length;
            });

            self.lastPage = ko.computed(function () {
                return Math.floor((self.totalItemCount() - 1) / self.perPage()) + 1;
            });

            self.hasNextPage = ko.computed(function () {
                return self.currentPage() < self.lastPage();
            });

            self.hasPrevPage = ko.computed(function () {
                return self.currentPage() > 1;
            });

            self.pager = ko.computed(function () {
                var pagesArray = [],
                    i,
                    pageCount = self.lastPage(),
                    from = Math.max(1, self.currentPage() - self.getFrameStart()),
                    to = Math.min(pageCount, self.currentPage() + self.getFrameEnd()),
                    pageFrom = Math.max(1, Math.min(to - self.pageFrame, from)),
                    pageTo = Math.min(pageCount, Math.max(from + self.pageFrame, to));

                self.frameStart(pageFrom);
                self.frameEnd(pageTo);

                for (i = pageFrom; i <= pageTo; i++) {
                    pagesArray.push(i);
                }

                return pagesArray;
            });

            self.canShowPreviousJump = ko.computed(function () {
                return self.getPreviousJumpPage() !== null;
            });

            self.canShowNextJump = ko.computed(function () {
                return self.getNextJumpPage() !== null;
            });
        },
        getFrameStart: function () {
            return Math.floor(this.pageFrame / 2);
        },
        getFrameEnd: function () {
            return Math.ceil(this.pageFrame / 2);
        },
        currentPage: locations.currentPage,
        locations: locations,
        prevItem: function () {
            this.currentPage(this.currentPage() - 1);
        },
        nextItem: function () {
            this.currentPage(this.currentPage() + 1);
        },
        isCurrentPage: function (item) {
            return item === this.currentPage();
        },
        goToPage: function (page) {
            this.currentPage(page);
        },
        getJump: function () {
            return parseInt(this.pageJump, 10);
        },
        canShowFirst: function () {
            return this.getJump() > 1 && this.frameStart() > 1;
        },
        canShowLast: function () {
            return this.getJump() > 1 && this.frameEnd() < this.lastPage();
        },
        getPreviousJumpPage: function () {
            if (!this.getJump()) {
                return null;
            }

            var $frameStart = this.frameStart();
            if ($frameStart - 1 > 1) {
                return Math.max(2, $frameStart - this.getJump());
            }

            return null;
        },
        getNextJumpPage: function () {
            if (!this.getJump()) {
                return null;
            }

            var $frameEnd = this.frameEnd();
            if (this.lastPage() - $frameEnd > 1) {
                return Math.min(this.lastPage() - 1, $frameEnd + this.getJump());
            }

            return null;
        },
        showDetails: function (locations, e) {
            if (!locations.settings().open_in_popup) return true;

            e.preventDefault();
            this.details().location = locations;
            events.trigger('location.show', locations, locations.settings());
        },
        onRenderList: function () {
//            this.getPopUp().closeModal();
        }
    });
});
