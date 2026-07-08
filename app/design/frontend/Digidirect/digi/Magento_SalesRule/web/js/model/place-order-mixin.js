/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Magento_SalesRule/js/model/coupon',
    'Magento_Checkout/js/action/get-totals'
], function ($, wrapper, quote, coupon, getTotalsAction) {
    'use strict';

    return function (placeOrderAction) {

        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {

            return originalAction(paymentData, messageContainer);
        });
    };
});
