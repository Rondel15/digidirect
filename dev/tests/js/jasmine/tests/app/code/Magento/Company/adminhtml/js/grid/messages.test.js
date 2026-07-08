/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'ko',
    'escaper',
    'Magento_Company/js/grid/messages'
], function ($, ko, Escaper, Messages) {
    'use strict';

    describe('Magento_Company/js/grid/messages', function () {
        let obj,
            escaperInstance,
            successMsgType = 'success',
            successMsg = 'Works fine.',
            errorMsgType = 'error',
            errorMsg = 'Something went wrong.',
            expectedSuccessMsg = {code: 'success', 'message': 'Works fine.'},
            expectedErrorMsg = {code: 'error', 'message': 'Something went wrong.'};

        beforeEach(function () {
            escaperInstance = Escaper;
            obj = Messages({
                escaper: escaperInstance
            });
        });

        it('add success message', function () {
            obj.add(successMsgType, successMsg);
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([expectedSuccessMsg]));
        });

        it('add error message', function () {
            obj.add(errorMsgType, errorMsg);
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([expectedErrorMsg]));
        });

        it('add multiple messages', function () {
            obj.add(successMsgType, successMsg);
            obj.add(errorMsgType, errorMsg);
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([
                expectedSuccessMsg,
                expectedErrorMsg
            ]));
        });

        it('clean messages', function () {
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([]));
            obj.add(successMsgType, successMsg);
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([expectedSuccessMsg]));
            obj.removeAll();
            expect(JSON.stringify(obj.get())).toEqual(JSON.stringify([]));
        });

        it('escape HTML msg', function () {
            let htmlMsg = '<scrip>alert("Test!") </scrip>Works <b>fine</b>',
                expectedMsg = 'alert("Test!") Works <b>fine</b>';

            expect(obj.prepareHtmlMsg(htmlMsg)).toEqual(expectedMsg);
        });
    });
});
