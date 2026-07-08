/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'ko',
    'squire'
], function (ko, Squire) {
    'use strict';

    describe('Magento_CompanyShipping/js/form/element/select/select-company-shipping-method', function () {
        var availableShippingsField,
            params,
            testObj,
            injector = new Squire();

        beforeEach(function (done) {
            params = {
                dataScope: '',
                availableShippingsFieldName: 'testAvailableShippingsField',
                applicableShippingMethods: {
                    b2b: '0',
                    allEnabled: '1'
                },
                b2bShippingMethods: 'Method 1,Method 3',
                options: [
                    {label: 'B2B', value: '0'},
                    {label: 'All', value: '1'},
                    {label: 'Selected', value: '2'}
                ],
                value: '0'
            };

            injector.require([
                'Magento_CompanyShipping/js/form/element/select/select-company-shipping-method',
                'uiRegistry'
            ], function (SelectShipping, registry) {
                availableShippingsField = {
                    value: ko.observable([]),
                    disabled: ko.observable(false),
                    set: jasmine.createSpy(),
                    on: jasmine.createSpy(),
                    get: jasmine.createSpy(),
                    initialOptions: [
                        {label: 'Method 1', value: '1'},
                        {label: 'Method 2', value: '2'},
                        {label: 'Method 3', value: '3'}
                    ],
                    initialValue: []
                };
                registry.set('testAvailableShippingsField', availableShippingsField);
                testObj = new SelectShipping(params);
                done();
            });
        });

        afterEach(function () {
            injector.clean();
        });

        afterAll(function () {
            injector.remove();
        });

        describe('"initialize" method', function () {
            it('Check for defined', function () {
                expect(testObj.initialize).toBeDefined();
                expect(testObj.initialize).toEqual(jasmine.any(Function));
            });

            it('Check _selectAvailableShippingsFieldOptions call on initialize', function () {
                spyOn(testObj, '_selectAvailableShippingsFieldOptions');
                testObj.initialize();
                expect(testObj._selectAvailableShippingsFieldOptions).toHaveBeenCalled();
            });
        });

        describe('"onUpdate" method', function () {
            it('Check for defined', function () {
                expect(testObj.onUpdate).toBeDefined();
                expect(testObj.onUpdate).toEqual(jasmine.any(Function));
            });

            it('Check _selectAvailableShippingsFieldOptions method call into onUpdate', function () {
                spyOn(testObj, '_selectAvailableShippingsFieldOptions');
                testObj.onUpdate();
                expect(testObj._selectAvailableShippingsFieldOptions).toHaveBeenCalled();
            });
        });

        describe('Change availableShippingsField state', function () {
            it('self.disabled = false, self.value = applicableShippingMethods.b2b', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('0');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = false, self.value = applicableShippingMethods.allEnabled', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('1');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = false, self.value = "specific"', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('2');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(false);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', false, jasmine.anything());
            });

            it('self.disabled = true, self.value = applicableShippingMethods.b2b', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('0');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = true, self.value = applicableShippingMethods.allEnabled', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('1');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = true, self.value = "specific"', function () {
                spyOn(testObj, 'availableShippingsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('2');
                testObj._updateAvailableShippingsFieldState();
                expect(testObj.availableShippingsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().set)
                    .toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });
        });

        describe('"_getInitialAvailableShippingsOptions" method', function () {
            it('Check for defined', function () {
                expect(testObj._getInitialAvailableShippingsOptions).toBeDefined();
                expect(testObj._getInitialAvailableShippingsOptions).toEqual(jasmine.any(Function));
            });

            it('Check get value', function () {
                expect(testObj._getInitialAvailableShippingsOptions()).toEqual(['1', '2', '3']);
            });
        });

        describe('"_getSelectedShippingMethods" method', function () {
            it('Check for defined', function () {
                expect(testObj._getSelectedShippingMethods).toBeDefined();
                expect(testObj._getSelectedShippingMethods).toEqual(jasmine.any(Function));
            });

            it('Check get value', function () {
                expect(testObj._getSelectedShippingMethods()).toEqual(['Method 1', 'Method 3']);
            });
        });

        describe('"_selectAvailableShippingsFieldOptions" method', function () {
            it('Check for defined', function () {
                expect(testObj._selectAvailableShippingsFieldOptions).toBeDefined();
                expect(testObj._selectAvailableShippingsFieldOptions).toEqual(jasmine.any(Function));
            });

            it('Select "B2B" methods', function () {
                spyOn(testObj.availableShippingsField(), 'disabled');
                spyOn(testObj.availableShippingsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('0');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailableShippingsFieldOptions();
                expect(testObj.availableShippingsField().disabled).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().value).toHaveBeenCalledWith(['Method 1', 'Method 3']);
            });

            it('Select "All" methods', function () {
                spyOn(testObj.availableShippingsField(), 'disabled');
                spyOn(testObj.availableShippingsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('1');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailableShippingsFieldOptions();
                expect(testObj.availableShippingsField().disabled).toHaveBeenCalledWith(true);
                expect(testObj.availableShippingsField().value).toHaveBeenCalledWith([ '1', '2', '3' ]);
            });

            it('Select "Specific" methods', function () {
                spyOn(testObj.availableShippingsField(), 'disabled');
                spyOn(testObj.availableShippingsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('2');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailableShippingsFieldOptions();
                expect(testObj.availableShippingsField().disabled).toHaveBeenCalledWith(false);
                expect(testObj.availableShippingsField().value).toHaveBeenCalledWith([]);
            });
        });
    });
});
