/* eslint no-useless-escape: [0] */
define([
    'jquery',
    'underscore',
    'Digidirect_MyStoreWidget/js/dist/common/component',
    'jquery/ui',
    'mage/translate',
    'mage/validation'
], function ($, _, Component) {
    'use strict';
    return function (widget) {
        $.widget('digidirect.myStoreSwitcher', widget, {
            options: {
                isAjaxSave: true,
                searchNoResults: '<span id="no_result_notice">' + $.mage.__('No matches found.') + '</span>'
            },

            getAutocompeleteItemFormat: function (ul, item) {
                var aimPoint = $('<li class="item" id="info-store-item">');
                aimPoint.append($('<a class="link">').html(item.label));
                var linkId = item.label.substring(10, 26);
                if (linkId !== 'no_result_notice') {
                    aimPoint.append($('<span class="link-choose-btn">' + $.mage.__('Select Store') + '</span>'));
                    aimPoint.append($('<span class="choosed-sign"></span>'));
                }
                return aimPoint.appendTo(ul);
            },

            onSuccessSave: function (data) {
                var self = this;
                if (!data.error) {
                    $(this.options.container).addClass(this.options.selectedSelector);
                    data.store.formatLabel = self.formatLabel(data.store);
                }
                $(document).trigger('mystore.save.success', [data]);
                setTimeout(function () {
                    $('.sdd-loader').css('display', 'none');
                    $('#mystore-control ul').css('pointer-events', 'auto');
                }, 3000);
            },

            onErrorSave: function (xhr, status) {
                $(document).trigger('mystore.save.error', [xhr, status]);
                setTimeout(function () {
                    $('.sdd-loader').css('display', 'none');
                    $('#mystore-control ul').css('pointer-events', 'auto');
                }, 3000);
            }
        });
        return $.digidirect.myStoreSwitcher;
    }
});
