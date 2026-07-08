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
                    this._EventListener();
                },

                /**
                 * Event open report popup
                 * @private
                 */
                _EventListener: function () {
                    var _this = this,
                        url = _this.options.url,
                        popup = $('#brand-report-popup'),
                        options = {
                            autoOpen: true,
                            responsive: true,
                            clickableOverlay: true,
                            type: 'popup',
                            title: 'Brand Revenue Report',
                            modalClass: 'mp-brand-report-popup',
                            innerScroll: true
                        };

                    $('body').on('click', '.mp-show-report', function (e) {
                        if (!popup.hasClass('popup-opened')) {
                            e.preventDefault();
                            $.ajax({
                                url: url,
                                type: 'GET',
                                showLoader: true,
                                success: function (res) {
                                    if (res.success) {
                                        popup.html(res.success).modal(options).modal('openModal');
                                        popup.addClass('popup-opened');
                                        popup.trigger('contentUpdated');
                                        $('body').trigger('contentUpdated');
                                    }
                                },
                                error: function (res) {
                                    popup.html(res.responseText);
                                }
                            });
                        }else{
                            popup.modal(options).modal('openModal');
                        }
                    });
                }
            }
        );

        return $.mageplaza.shopbybrand;
    }
);
