define([
    'jquery',
    'ko',
    'uiComponent'
], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        initialize: function () {
            //initialize parent Component
            this._super();
            this.qty = ko.observable(this.defaultQty);
        },

        decreaseQty: function() {
            var newQty = this.qty() - 1;
            if (newQty < 1) {
                newQty = 1;
            }
            return this.qty(newQty);
        },

        increaseQty: function() {
            
            var cartTotalQty = Number($('#cart-total-qty').html());
                
            if (cartTotalQty + this.qty() >= 10) {
                alert("Cart quantity limit reached!");
            } else {
                var newQty = this.qty() + 1;
                return this.qty(newQty);
            }
            
        }

    });
});