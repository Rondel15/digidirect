/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    '../model/quote',
    'Magento_Checkout/js/model/shipping-save-processor'
], function (quote, shippingSaveProcessor) {
    'use strict';

    function ensureValue(val, fallback) {
        return (val !== undefined && val !== null && val !== '') ? val : fallback;
    }


    return function () {
        var method = quote.shippingMethod && quote.shippingMethod();
        var selected = method ? (method.carrier_code + '_' + method.method_code) : null;

        if (selected === 'collect_collect') {
            var billing = typeof quote.billingAddress === 'function' ? quote.billingAddress() : null;
            var addr = typeof quote.shippingAddress === 'function' ? quote.shippingAddress() : null;

            addr = addr || {};

            addr.firstname = ensureValue(addr.firstname, ensureValue(billing && billing.firstname, 'Store'));
            addr.lastname  = ensureValue(addr.lastname,  ensureValue(billing && billing.lastname,  'Pickup'));
            addr.telephone = ensureValue(addr.telephone, ensureValue(billing && billing.telephone, '0000000000'));

            addr.street    = (addr.street && addr.street.length) ? addr.street : ['Store Pickup'];
            addr.city      = ensureValue(addr.city, 'Store Pickup');
            addr.postcode  = ensureValue(addr.postcode, '0000');

            addr.countryId = ensureValue(addr.countryId, ensureValue(billing && billing.countryId, 'AU'));
            addr.region    = ensureValue(addr.region,    ensureValue(billing && billing.region,    ''));
            addr.regionId  = ensureValue(addr.regionId,  ensureValue(billing && billing.regionId,  0));

            addr.save_in_address_book = 0;

            quote.shippingAddress(addr);
        }

        return shippingSaveProcessor.saveShippingInformation(quote.shippingAddress().getType());
    };
});
