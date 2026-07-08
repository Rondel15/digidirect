/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
/*jscs:disable jsDoc*/

define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_NegotiableQuote/js/action/place-order-negotiable-quote', function () {
        var injector = new Squire(),
            mocks = {
                'Magento_Checkout/js/model/place-order': jasmine.createSpy(),
                'mage/storage': jasmine.createSpy(),
                'Magento_Checkout/js/model/quote': {
                    getQuoteId: function () {
                        return 1;
                    },
                    billingAddress: function () {
                        return [];
                    }
                },
                'Magento_Checkout/js/model/url-builder': {
                    createUrl: function () {
                        return 'url';
                    }
                },
                'Magento_Checkout/js/model/error-processor': jasmine.createSpy(),
                'Magento_Checkout/js/model/full-screen-loader': {
                    startLoader: function () {
                        return true;
                    }
                }
            },
            checkout;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require(['Magento_NegotiableQuote/js/action/place-order-negotiable-quote'], function (action) {
                checkout = action;
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        it('test placeOrder model used with NegotiableQuote order', function () {
            checkout({
                cart: {}
            });
            expect(mocks['Magento_Checkout/js/model/place-order']).toHaveBeenCalled();
        });
    });
});
