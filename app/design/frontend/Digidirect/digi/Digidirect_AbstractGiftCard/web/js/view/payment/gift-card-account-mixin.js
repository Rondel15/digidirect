define([
    'Magento_Checkout/js/model/totals'
], function (
    totals
) {
    'use strict';

    var mixin = {
        getTotals: function () {
            let total = totals.totals().base_grand_total;
            if (total) {
                return total;
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
