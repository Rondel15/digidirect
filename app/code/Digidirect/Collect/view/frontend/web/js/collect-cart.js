define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'jquery/ui'
], function ($, customerData) {
    'use strict';

    $.widget('digidirect.collectCart', {
        options: {
            storeName: '[data-role="collect-store"]',
            collectTrigger: '[data-role="collect-trigger"]',
            deliveryTrigger: '[data-role="delivery-trigger"]',
            dataAction: '',
            collectPlaceId: '',
            productId: '',
            quoteItemId: '',
            deliveryInstead: '',
            collectModal: '#collect_modal_cart'
        },

        _create: function () {
            this._bind();
        },

        /**
         * Bind events
         * @private
         */
        _bind: function () {
            this.element.find(this.options.collectTrigger).on('click', $.proxy(this._setNewCollectData, this));
            this.element.find(this.options.deliveryTrigger).on('click', $.proxy(this.setDelivery, this));
        },

        /**
         * Transfer item data to collect's modal widget
         * @private
         */
        _setNewCollectData: function () {
            var self = this,
                data = {
                    collectPlaceId: self.options.collectPlaceId,
                    productId: self.options.productId,
                    quoteItemId: self.options.quoteItemId,
                    dataAction: self.options.dataAction,
                    provider: self
                };

            $(this.options.collectModal).collectModal('setNewCollectData', data);
        },

        /**
         * Change the store's name
         * @param {object} data
         */
        changeCollectStore: function (data) {
            var storeName = this.element.find(this.options.storeName);
            if (storeName.length > 0) {
                storeName.text(data.collect_place_name);
                this.options.collectPlaceId = data.collect_place_id;
            } else {
                this.updateCustomerData();
            }
        },

        /**
         * Reload page for apply the changes
         * @private
         */
        _reloadPage: function () {
            window.location.reload();
        },

        /**
         * Set type delivery
         * @return {boolean}
         */
        setDelivery: function (e) {
            var self = this,
                url = this.options.deliveryInstead;

            $.ajax({
                url: url,
                success: function (data) {
                    if (data.result) {
                        self.updateCustomerData();
                    } else {
                        console.warn(data.message);
                    }
                }}
            );
            e.preventDefault();
        },

        /**
         * Update Customer Data
         */
        updateCustomerData: function () {
            customerData.reload('cart-data', true);
            $(document).on('customer-data-reload', $.proxy(this._reloadPage, this));
        }
    });

    return $.digidirect.collectCart;
});
