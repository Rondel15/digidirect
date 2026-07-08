define([
    'jquery',
    'Digidirect_QuickView/js/dist/common/component',
    'jquery/ui'
], function ($, Component) {
    'use strict';
    $.widget('digidirect.quickView', {
        options: {
            button: '[data-role=quickview-button]',
            contentContainer: '[data-role=quickview-content]',
            iframe: '[data-role=quickview-window]',
            loaderContainer: '.page-wrapper',
            itemSelector: '[data-container=product-grid]',
            mobileListener: '.product-item-photo',
            productForm: '#product_addtocart_form',
            modal: {
                type: 'popup',
                buttons: []
            }
        },
        _create: function () {
            new Component(this.options);     
        }
    });
    return $.digidirect.quickView;
});
