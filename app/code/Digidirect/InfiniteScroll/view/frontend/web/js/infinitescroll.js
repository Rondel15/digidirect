define([
    'jquery',
    'Digidirect_InfiniteScroll/js/dist/common/component',
    'catalogAddToCart',
    'mage/translate'
], function ($, Component) {
    'use strict';

    $.widget('digidirect.infinitescroll', {
        options: {
            itemsContainerSelector: '.product-items',
            itemSelector: '> .item',
            rememberScrollState: false,
            scrollStateKey: 'infinite-scroll-state',
            scrollToLastViewedItem: false,
            itemUrlSelector: '.product-item-link, .product-item-photo',
            itemUrlKey: 'infinite-item-url',
            nextUrl: null,
            action: 'click',
            buttonArea: '.products.wrapper',
            buttonPrepend: false,
            buttonContent: $.mage.__('Load More'),
            scrollContainer: $(window),
            scrollOffset: 150,
            requestOptions: {},
            preFill: false,
            viewCustom: '',
            addOn: ''
        },

        _create: function () {
            new Component(this.options);
        }
    });

    return $.digidirect.infinitescroll;
});
