/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'jquery',
    'squire'
], function ($, Squire) {
    'use strict';

    var injector = new Squire(),
        mocks = {
            'Magento_QuickOrder/js/item-table/mass-add-rows': {
                addNewRows: jasmine.createSpy().and.returnValue({
                    done: jasmine.createSpy().and.returnValue($.Deferred().promise())
                })
            }
        },
        obj,
        skuItem,
        errorType = 'example_error_type';

    describe('Magento_QuickOrder/js/file', function () {

        beforeEach(function (done) {
            var html = '<div class="deletable-item">' +
                '<input type="text" data-role="product-sku" value="sku1"/>' +
                '<input type="number" data-role="product-qty" value="1"/>' +
                '</div>';

            skuItem = $(html);
            $(document.body).append(skuItem);

            injector.mock(mocks);
            injector.require(['Magento_QuickOrder/js/file'], function (File) {
                obj = new File({
                    errorType: errorType
                });
                done();
            });

            jQuery.post = jasmine.createSpy().and.callFake(function () {
                var d = $.Deferred();

                d.resolve([
                    {
                        items: {
                            'value1': {
                                sku: 'value1'
                            }
                        }
                    },
                    'success'
                ]);

                return d.promise();
            });
        });

        afterEach(function () {
            skuItem.remove();
        });

        describe('"_displaySkus" method', function () {
            it('Check for defined', function () {
                expect(obj._displaySkus).toBeDefined();
                expect(obj._displaySkus).toEqual(jasmine.any(Function));
            });

            it('Check post request sending successfully', function () {
                var contents = 'sku,qty\nvalue1,1\nvalue2,1',
                    localParams = {
                        items: JSON.stringify([
                            {
                                'sku': 'value1',
                                'qty': 1
                            },
                            {
                                'sku': 'value2',
                                'qty': 1
                            }
                        ]),
                        errorType: errorType
                    };

                obj._displaySkus(contents);
                expect(jQuery.post).toHaveBeenCalledWith('', localParams);
            });

            it('Check quantity is calculated correctly', function () {
                var contents = 'sku,qty\nsku1,1.5\nsku2,1\nsku1,1',
                    localParams = {
                        items: JSON.stringify([
                            {
                                'sku': 'sku1',
                                'qty': 2.5
                            },
                            {
                                'sku': 'sku2',
                                'qty': 1
                            },
                            {
                                'sku': 'sku1',
                                'qty': 1
                            }
                        ]),
                        errorType: errorType
                    };

                obj._displaySkus(contents);
                expect(jQuery.post).toHaveBeenCalledWith('', localParams);
            });
        });

        describe('Test "_getSingleSkuInput" method', function () {
            beforeEach(function () {
                $('body').append('<div class="fields additional deletable-item" data-role="new-block">' +
                    '<div class="control">' +
                    '<input type="text" name="items[0][sku]" id="id-items0sku" data-id="0sku" data-sku="true" ' +
                    'data-role="product-sku" className="ui-autocomplete-input" autoComplete="off">' +
                    '</div><div class="control">' +
                    '<input type="number" name="items[0][qty]" id="id-items0qty" class="qty" maxlength="13" ' +
                    'aria-required="true" data-role="product-qty"></div>' +
                    '<button type="button" class="action remove" title="Remove Row"' +
                    ' data-role="delete"><span>Remove Row</span></button></div>');
            });

            it('Check delete button css attribute display is inline-block', function () {
                obj._getSingleSkuInput();
                expect($('body').find('button[data-role="delete"]').css('display')).toEqual('inline-block');
            });
        });
    });
});
