define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (target) {
        $.widget('mage.slide', target, {
            _show: function () {
                $('html, body').animate({
                    scrollTop: $(this.options.bundleOptionsContainer).offset().top
                }, 600);
                $('#product-options-wrapper > fieldset').focus();
            },
            _hide: function () {
                $('html, body').animate({
                    scrollTop: 0
                }, 600);

                if($('body').hasClass('quickview-main')) {
                    $('.quickview-main .mCSB_container, .quickview-main .mCSB_dragger').animate({
                        top: 0
                    }, 600);
                }
            }
        });

        return $.mage.slide;
    }
});
