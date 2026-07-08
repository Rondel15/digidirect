define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('Digidirect.productOverlay', widget, {
            options: {
                productConteiner: '[data-parent-product-id]',
                isHideAllWithoutParent: true
            },
            /**
             * Hide overlay without disabled flag
             */
            setOverlayStyle: function () {
                this._super();

                // hide overlay if it does not have use for parent flag
                if (this.options.isHideAllWithoutParent) {
                    this.showOnlyParentProductOverlay();
                    return;
                }
                if (this.options.hideForConfigurable) {
//                    this.element.addClass('-hide');
                }
            },
            showOnlyParentProductOverlay: function () {
                var parentProductId = this.getParentProductId();
                if (parentProductId && +this.element.data('product-id') === +parentProductId) {
                    this.element.removeClass('-hide');
                    return;
                }
//                this.element.addClass('-hide');
            },
            getParentProductId: function () {
                return this.element.closest(this.options.productConteiner).data('parent-product-id') || undefined;
            }
        });
        return $.Digidirect.productOverlay;
    };
});
