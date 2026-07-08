define([
    'jquery',
    'jquery/ui',
    'catalogAddToCart'
], function ($) {
    $.widget('mage.catalogAddToCart', $.mage.catalogAddToCart, {
        options: {
            bindUpdateCart: false,
            isCloseAfterAddToCart: false
        },
        ajaxSubmit: function (form) {
            if (this.options.bindUpdateCart && !this.options.isCloseAfterAddToCart) {
                this._bindUpdateCart();
            }
            if (this.options.isCloseAfterAddToCart) {
                window.globalStore.trigger(window.globalEvents.QUICK_VIEW_CLOSE);
                window.globalStore.trigger(window.globalEvents.ADD_TO_CART, this, this.options, form);
            } else {
                this._super(form);
            }
        },
        _bindUpdateCart: function () {
            $(document).on('ajaxComplete', function (event, xhr) {
                if (xhr.responseJSON && 'cart' in xhr.responseJSON) {
                    window.globalStore.trigger(window.globalEvents.SECTION_UPDATE, ['cart']);
                }
            });
        }

    });
    return $.mage.catalogAddToCart;
});
