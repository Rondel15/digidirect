define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            options: {
                applicableSimple: {},
                productContainer: '.product-item-info',
                labelContainer: '[data-role="label-overlay"]',
                pdpMediaContainer: null
            },
            labelsForParent: '',
            _create: function () {
                this._super();
                this._findProductOverlay();
                var labels = this.options.parentLabels;
                if (labels) {
                    for (var key in labels) {
                        if (labels.hasOwnProperty(key)) {
                            this.labelsForParent += labels[key];
                        }
                    }
                }

                this._bindOverlay();
            },

            /**
             * Bind overlay append
             * @private
             */
            _bindOverlay: function () {
                if (!this.inProductList) {
                    $(this.element).on('product.overlay.appended', $.proxy(function () {
                        this._findProductOverlay();
                    }, this));
                }
            },

            /**
             * Find overlays
             * @private
             */
            _findProductOverlay: function () {
                var simpleId = this.getProduct(),
                    counter = this.getProductCounter(),
                    productContainer = this.options.pdpMediaContainer ? $(this.options.pdpMediaContainer) : $(this.element).closest(this.options.productContainer),
                    overlays = productContainer.find('.product-overlay');

                if (this.options.isLoaded) {
                    $(this.options.labelContainer).empty();
                } else {
                    this.options.isLoaded = true;
                }

                this.hideAllOverlays(overlays);

                if (!simpleId) {
                    this.showOverlaysForParent(overlays);
                    this.setStockLabelForParent();
                } else if (counter === 1) {
                    this.showOverlaysForCurrentProduct(simpleId, overlays);
                }
            },

            /**
             * Show overlays for parent product
             * @param overlays
             */
            showOverlaysForParent: function (overlays) {
                overlays.filter('[data-role="use-for-parent"]').removeClass('-hide');
            },

            /**
             * Set stock label for parent product
             */
            setStockLabelForParent: function () {
                if (this.labelsForParent !== '') {
                    $(this.options.labelContainer).html(this.labelsForParent);
                }
            },

            /**
             * Show overlays for current simple product
             * @param simpleId
             * @param overlays
             */
            showOverlaysForCurrentProduct: function (simpleId, overlays) {
                var stockLabel = '';
                if (this.options.applicableSimple[simpleId]) {
                    _.each(this.options.applicableSimple[simpleId], function (item) {
                        overlays.filter('[data-overlay-id=' + item.overlay_id + ']').removeClass('-hide');
                        stockLabel += item.stock_label;
                    }, this);
                }
                $(this.options.labelContainer).html(stockLabel);
            },

            /**
             * Hide all overlays
             * @param overlays
             */
            hideAllOverlays: function (overlays) {
//                overlays.addClass('-hide');
            },

            getProductCounter: function () {
                var products = this._CalcProducts();

                return products.length;
            },

            updateBaseImage: function (images, context, isInProductView) {
                this._super(images, context, isInProductView);
                this._findProductOverlay();
            }
        });
        return $.mage.SwatchRenderer;
    };
});
