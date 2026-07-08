define([
    'jquery',
    'underscore',
    'Magento_Customer/js/customer-data',
    'mage/template',
    'text!Digidirect_Collect/template/places.html',
    'jquery/ui'
], function ($, _, customerData, mageTemplate, placeTemplate) {
    'use strict';

    $.widget('digidirect.collectBlock', {
        options: {
            collectBlock: '[data-role="collect-block"]',
            collectType: '[data-collect-type]',
            triggerElement: '[data-role="trigger-collect"]',
            visibleClass: '-visible',
            isSingleStore: '',
            deliveryMessage: '[data-role="collect-delivery-message"]',
            collectMessage: '[data-role="collect-collect-message"]',
            searchPlaces: '[data-role="check-collect-place"]',
            searchUrl: '',
            collectQty: '1',
            placeContainer: '.collect_place_div_table'
        },

        _create: function () {
            this._bind();
            this._searchPlace();
            this._setPreselectedCollect();
        },

        _bind: function () {
            var self = this;
            this.element.find(this.options.triggerElement).on('change', function () {
                $(self.options.collectBlock).removeClass(self.options.visibleClass);
                $('[data-collect-type="' + $(this).val() + '"]').addClass(self.options.visibleClass);
            });
        },

        _setPreselectedCollect: function () {
            var self = this;
            this.collectData = customerData.get('digidirect_collect')().preselected_shipping_method;

            if (!this.collectData) {
                customerData.get('digidirect_collect').subscribe(function (newValue) {
                    self._setCollectType(newValue.preselected_shipping_method);
                });
            } else {
                this._setCollectType(this.collectData);
            }
        },

        _setCollectType: function (data) {
            if (this.options.isSingleStore && data) {
                var cartItems = customerData.get('cart')().items;

                this.element.find('[value="' + data + '"]').prop('checked', true);
                $('[data-collect-type="' + data + '"]').addClass(this.options.visibleClass);

                if (cartItems && cartItems.length > 1) {
                    this.toggleTypeMessage(data);
                }
            }
        },

        toggleTypeMessage: function (data) {
            if (data === 'delivery') {
                this.element.find('[value="collect"]').prop('disabled', true);

                $(this.options.deliveryMessage).show();
                $(this.options.collectMessage).hide();
            } else {
                $(this.options.deliveryMessage).hide();
                $(this.options.collectMessage).show();
            }
        },

        _searchPlace: function () {
            var self = this;

            $(this.options.searchPlaces).on('click', function (e) {
                e.preventDefault();
                var $form = $(this).closest('form');
                if ($form.length) {
                    if (!$form.valid()) {
                        return;
                    }
                }
                self._sendRequest();
            });
        },

        _sendRequest: function () {
            var self = this,
                url = this.options.searchUrl;

            $.ajax({
                url: url,
                data: this._getFormData(),
                success: function (result) {
                    $(self.options.placeContainer).html(mageTemplate(placeTemplate, {
                        data: result.data
                    }));
                    $(self.options.placeContainer).show();

                    $('[name=collect_place_id]').on('click', function () {
                        $('#collect_place_storage_name').val($(this).attr('data-collectplace-storage-name'));
                    });
                }
            });
        },

        _getFormData: function () {
            var $addToCartForm = $('#product_addtocart_form'),
                postcode = $('[data-role=postcode]').val(),
                distance = $('#collect_distance').val(),
                collectProductId = $('#collect_product_id').val(),
                collectSimpleProductId = $('#collect_simple_product_id').val(),
                productId = $addToCartForm.find('[name=product]').val(),
                simpleProductId = $('#selected_configurable_option').find('[name=product]').val(),
                quoteItemId = $('#collect_quote_item_id').val(),
                collectQty = $addToCartForm.find('[name=qty]').val();

            if (typeof collectProductId === 'undefined' || collectProductId === '') {
                collectProductId = productId;
            }
            if (typeof collectSimpleProductId === 'undefined' || collectSimpleProductId === '') {
                collectSimpleProductId = simpleProductId;
            }

            if (typeof collectQty === 'undefined' || collectQty === '') {
                collectQty = this.options.collectQty;
            }

            return {
                collect_postcode: postcode,
                collect_distance: distance,
                collect_qty: collectQty,
                product_id: collectProductId,
                simple_product_id: collectSimpleProductId,
                quote_item_id: quoteItemId
            };
        }
    });

    return $.digidirect.collectBlock;
});
