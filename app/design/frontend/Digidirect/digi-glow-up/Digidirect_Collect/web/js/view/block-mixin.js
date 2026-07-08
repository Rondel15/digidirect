define([
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'jquery',
    'Magento_Checkout/js/model/cart/estimate-service',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore'
], function (ko, Component, quote, $, estimate, async, _) {
    'use strict';

    return function (target) {
        var checkIsWeb = window.hasWebOnly;

        return target.extend({
            defaults: {
                isVsmAvailable: ko.observable(false),
            },
            isWebOnly: ko.observable(checkIsWeb || false),

            initialize: function () {
                this._super();
                this.checkInstantPickUp();

            },
            _isCheckoutPage: function () {
                return $('body').hasClass('checkout-index-index');
            },
            onSuccessApplyPlace: function (response) {
                this._super(response);
                var pageTypeIsCheckout = this._isCheckoutPage();
                if (pageTypeIsCheckout) {
                    window.selectStore(false);
                }
            },
            onSuccessDelivery: function () {
                this._super();
                this.toggleToDeliveryShippingMethod();
            },
            toggleToDeliveryShippingMethod: function () {
                var closestRadioButtons = $('.row.collect').siblings().find('input');

                if (!closestRadioButtons.filter(':checked').length) {
                    closestRadioButtons.first().trigger('click');
                }
            },
            setCollectAbstractEntityFields: function () {
                var self = this,
                    flag = true;
                this._super();
                if (this.isSingleCartCollectVariation() && this.collectPlaces() && !this.collectPlaces().length && this._isCheckoutPage()) {
                    async.async('.row .radio.-item-0', function (node) {
                        if (flag && $(node).is(':visible')) {
                            $(node).trigger('click');
                            flag = false;
                        }
                    });
                }

                if (this.isWebOnly() === 1) {
                    async.async('#collect_type_delivery', function (node) {
                        $(node).trigger('click');
                    });
                }
            },
            selectCollectShippingMethod: function () {
                var flag = true;

                async.async('div[data-collect-type="delivery"]', function (node) {
                    $(node).removeClass('-visible');
                });

                async.async('input[value="collect_collect"]', function (node) {
                    if (flag) {
                        $(node).trigger('click');
                        flag = false;
                    }
                });

                async.async('div[data-collect-type="collect"]', function (node) {
                    $(node).addClass('-visible');
                });
            },

            checkInstantPickUp: function () {
                if (this.collectPlaces().length > 0) {
                    var currentCCStore = _.first(this.collectPlaces());
                    var currentStoreAddress = this.getCurrentStoreAddress(currentCCStore);
                    this.getVsmShippingDataFromServer(currentStoreAddress);
                }
                this.collectPlaces.subscribe(function (newStores) {
                    if (_.isArray(newStores)) {
                        var currentCCStore = _.first(newStores);
                        var currentStoreAddress = this.getCurrentStoreAddress(currentCCStore);
                        this.getVsmShippingDataFromServer(currentStoreAddress);
                    }
                }.bind(this));
            },

            getCurrentStoreAddress: function (currentCCStore) {
                if (_.isObject(currentCCStore) && _.has(currentCCStore, 'collect_prefill_shipping_fields')) {
                    return  currentCCStore.collect_prefill_shipping_fields;
                }
                return null;
            },

            getVsmShippingDataFromServer: function (currentStoreAddress) {
                if (_.isNull(currentStoreAddress)) {
                    return ;
                }
                var country = currentStoreAddress.country_id;
                var postcode = currentStoreAddress.postcode;
                $.getJSON('/rest/V1/check_vsm_availability/' + country + '/code/' + postcode)
                    .done(function (data) {
                        var responseObj = JSON.parse(data);
                        if (_.has(responseObj, 'error')) {
                            console.log(data);
                            return;
                        }
                        this.checkAvailableVsmData(responseObj, postcode);
                    }.bind(this))
                    .fail(function (response) {
                        console.log(response);
                    })
            },

            checkAvailableVsmData: function (responseObject, postcode) {
                if (_.isObject(responseObject) && _.has(responseObject, 'vsmCheckResult')) {
                    if (_.has(responseObject.vsmCheckResult, postcode)) {
                        this.isVsmAvailable(responseObject.vsmCheckResult[postcode]);
                    }
                }
            }
        });
    }
});
