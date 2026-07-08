define([
    'jquery',
    'matchMedia',
    'mage/template',
    'jquery/ui',
    'mage/dataPost',
    'customScrollbarInit'
], function ($, mediaCheck, mageTemplate ) {
    'use strict';

    return function (target) {
        $.widget('digidirect.quickViewPageEvents', target, {
            _create: function () {
                $('html').addClass('full-height');
                $('body').dataPost('disable');
                mediaCheck({
                    media: '(min-width: 1024px)',
                    entry: function () {
                        $('body').find('.columns').customScrollbar();
                    }
                });
                this._bind();
            }
        });

        return $.digidirect.quickViewPageEvents;
    };
});