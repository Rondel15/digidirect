define([
    'jquery',
    'mage/translate',
    'jquery/ui',
    'Magento_Ui/js/modal/modal',
    'collectCart'
], function ($, $t) {
    'use strict';

    $.widget('digidirect.collectModal', {
        options: {
            collectModal: '[data-role="collect-modal"]',
            modalTitle: $t('Find collect store place'),
            modalButtonText: $t('Buy and Collect'),
            modalButtonClass: 'action-primary',
            collectPlace: '#cart_collect_place_id',
            collectProduct: '#collect_product_id',
            collectQuote: '#collect_quote_item_id',
            collectStoreInput: '[name="collect_place_id"]',
            collectDeliveryTypeInput: '[data-role="trigger-collect"]',
            collectItem: 'collect-item_'
        },

        _create: function () {
            this._initModal();
        },

        /**
         * Int modal
         * @private
         */
        _initModal: function () {
            var self = this,
                modalConfig = {
                    title: self.options.modalTitle,
                    autoOpen: false,
                    buttons: [{
                        text: self.options.modalButtonText,
                        attr: {
                            'data-action': 'confirm'
                        },
                        class: this.options.modalButtonClass,
                        click: $.proxy(self._chackTypeDelivery, self)
                    }]

                };
            $(this.options.collectModal).modal(modalConfig);
        },

        /**
         * Set data
         * @param {object} data
         */
        setNewCollectData: function (data) {
            this.data = data;
            this.activeItem = $('[data-role=' + this.options.collectItem + this.data.productId + ']');
            $(this.options.collectPlace).val(data.collectPlaceId);
            $(this.options.collectProduct).val(data.productId);
            $(this.options.collectQuote).val(data.quoteItemId);
            $(this.options.collectModal).modal('openModal');
        },

        /**
         * Check selected method
         * @private
         */
        _chackTypeDelivery: function (e) {
            var deliveryType = $(this.options.collectDeliveryTypeInput + ':checked').val();
            if (deliveryType === 'collect') {
                this._changeCollectStore();
            }

            if (deliveryType === 'delivery') {
                this.activeItem.collectCart('setDelivery', e);
                $(this.options.collectModal).modal('closeModal');
            }
        },

        /**
         * Send data for new store
         * @private
         */
        _changeCollectStore: function () {
            var self = this,
                url = this.data.dataAction,
                newStore = $(self.options.collectStoreInput + ':checked').val(),
                data = {
                    quote_item_id: self.data.quoteItemId,
                    collect_place_id: newStore,
                    collect_place_storage_name: $(self.options.collectStoreInput + ':checked').attr('data-collectplace-storage-name')
                };

            if (newStore) {
                $.ajax({
                    url: url,
                    data: data,
                    success: function (data) {
                        if (data.data) {
                            self.activeItem.collectCart('changeCollectStore', data.data);
                            $(self.options.collectModal).modal('closeModal');
                        } else {
                            console.warn(data.result);
                        }
                    }
                });
            }
        }
    });

    return $.digidirect.collectModal;
});
