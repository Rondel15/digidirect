/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery',
        'mage/translate'
    ], function ($) {
        'use strict';
        $.widget(
            'mageplaza.shopbybrand', {
                _create: function () {
                    this._EventListener();
                },

                /**
                 * Event show list Product
                 * @private
                 */
                _EventListener: function () {
                    var url = this.options.url, _this = this, body = $('body'),
                        listProducts                               = $('#list-brand-products');

                    window.MpOnCompleteMassActionProduct = function () {
                        window.isGirdProductOpened = false;
                        $('#product_grid').remove();
                        $('.show-brand-products').trigger('click');
                    };

                    body.on('click', '.show-brand-products', function (e) {
                        listProducts.insertAfter(".show-brand-products");
                        if (!window.isGirdProductOpened) {
                            e.preventDefault();
                            _this.getListProducts(listProducts, url, _this);
                        } else if (listProducts.hasClass('to_hide')) {
                            listProducts.hide();
                            listProducts.removeClass('to_hide');
                        } else {
                            listProducts.show();
                            listProducts.addClass('to_hide');
                        }
                    });
                },
                getListProducts: function (listProducts, url, _this) {
                    $.ajax({
                        url: url,
                        type: 'GET',
                        data: {
                            option_id: $("#option_id").val(),
                            store_id: $("#store_id").val()
                        },
                        showLoader: false,
                        success: function (res) {
                            if (res) {
                                listProducts.find('#list-products-content').html(res);
                                //add param to mass action
                                var girdId = '#product_grid', idFrom = girdId + ' form';
                                $('input[name="form_key"]').clone().first().appendTo(idFrom);

                                $('.mp-message-add-product').remove();
                                window.isGirdProductOpened = true;
                                listProducts.addClass('to_hide');
                                listProducts.show();
                                $('body').trigger('contentUpdated');
                            }
                        },
                        error: function (res) {
                            listProducts.html(res.responseText);
                        }
                    });
                }
            }
        );

        return $.mageplaza.shopbybrand;
    }
);
