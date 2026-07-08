/**
 * Copyright © 2018 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
define([
    'jquery'
], function ($) {
    "use strict";

    return function () {
        $.validator.addMethod(
            'validate-itoris-price',
            function (value, elem) {
                var currentPrice = +elem.dataset.itorisCurrentPrice;

                if (parseFloat(value) >= currentPrice)
                    return false;

                if (parseFloat(value) <= 0)
                    return false;

                return true;
            },
            $.mage.__('The Matching Price should be lower than Our Price and Greather zero')
        );
    }
});
