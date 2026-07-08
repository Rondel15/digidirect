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
        'jquery'
    ], function ($) {
        'use strict';
        $.widget(
            'mageplaza.shopbybrand', {
                _create: function () {
                    this.options.gridId = '#mpbrand-grid-related_table ';
                    this._EventListener();
                    this.selectBrand();
                },

                /**
                 * Event show list Brands
                 * @private
                 */
                _EventListener: function () {
                    var url        = this.options.url,
                        listBrands = $('#list-related-brands');
                    $('body').on('click', '.show-related-brand', function (e) {
                        if (!window.isGirdRelatedOpened) {
                            listBrands.insertAfter(".show-related-brand");
                            e.preventDefault();
                            $.ajax({
                                url: url,
                                type: 'GET',
                                showLoader: true,
                                data: {
                                    option_id: $("#option_id").val(),
                                    store_id: $("#store_id").val()
                                },
                                success: function (res) {
                                    if (res) {
                                        listBrands.html(res);
                                        window.isGirdRelatedOpened = true;
                                        listBrands.addClass('to_hide');
                                        listBrands.show();
                                    }
                                },
                                error: function (res) {
                                    listBrands.html(res.responseText);
                                }
                            });
                        } else if (listBrands.hasClass('to_hide')) {
                            listBrands.hide();
                            listBrands.removeClass('to_hide');
                        } else {
                            listBrands.show();
                            listBrands.addClass('to_hide');
                        }
                    });
                }
                ,
                /**
                 * Init select Brand
                 */
                selectBrand: function () {
                    var brandId = [], girId = this.options.gridId;

                    // get All BrandIds mpbrand-grid-related'
                    $('body').delegate(girId + '.data-grid-actions-cell  .admin__control-checkbox', 'click', function () {
                        brandId = [];
                        $(girId + ' .admin__control-checkbox:checked').each(function () {
                            brandId.push($(this).attr('value'))
                        });
                        if (brandId[0] === 'on') {
                            brandId.splice(0, 1);
                        }
                        $("#related_brands").val(brandId);
                    });
                    // get BrandIds checked
                    $('body').delegate(girId + ' tbody tr input', 'click', function () {
                        brandId = [];
                        $(girId + ' .admin__control-checkbox:checked').each(function () {
                            brandId.push($(this).attr('value'))
                        });
                        $("#related_brands").val(brandId);
                    });
                    $('body').delegate(girId + ' tbody tr td:not(.col-index input)', 'click', function (e) {
                        if (!$(e.target).hasClass('checkbox')) {
                            $(this).parent().find('input').trigger('click');
                        }
                    });
                }
            }
        );

        return $.mageplaza.shopbybrand;
    }
);
