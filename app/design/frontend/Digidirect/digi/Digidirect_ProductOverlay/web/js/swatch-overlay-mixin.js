define([
    'jquery',
    'underscore',
    'jquery/ui',
    'Digidirect_ProductOverlay/js/swatch-overlay'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            options: {
                isShowOnlyThoseThatTheParentHas: true,
                parentProductId: null
            },
            _create: function () {
                this._super();
            },
            _findProductOverlay: function () {
                if (this.options.isShowOnlyThoseThatTheParentHas && this.options.parentProductId) {
                    this._findProductOverlayWhichHaveParentItemTo();
                    return;
                }
                this._super();
            },
            _findProductOverlayWhichHaveParentItemTo: function () {
                var simpleProductId = this.getProduct(),
                    counter = this.getProductCounter(),
                    $productContainer = $(this.element).closest(this.options.productConteiner),
                    overlayProduct = $productContainer.find('.product-overlay'),
                    parentProductId = this.options.parentProductId,
                    labelContainer = this.options.labelContainer;

                if (this.options.isLoaded) {
                    $(labelContainer).empty();
                } else {
                    this.options.isLoaded = true;
                }

//                overlayProduct.addClass('-hide');
                if (simpleProductId && typeof this.options.applicableSimple[simpleProductId] !== 'undefined' && counter === 1) {
                    $.each(this.options.applicableSimple[simpleProductId], function (i, overlay) {
                        //Change child product id to parent if exist (simpleProductId to parentProductId)
                        var overlayId = 'product-overlay-' + overlay.overlay_id + '-' + parentProductId,
                            stockLabel = overlay.stock_label,
                            labelResult = stockLabel + $(labelContainer).html();
                        $('.' + overlayId).removeClass('-hide');
                        $(labelContainer).html(labelResult);
                    });
                } else {
                    $productContainer.find('.product-overlay').filter('[data-product-id=' + parentProductId + ']').removeClass('-hide');
                }
            }
        });
        return $.mage.SwatchRenderer;
    };
});
