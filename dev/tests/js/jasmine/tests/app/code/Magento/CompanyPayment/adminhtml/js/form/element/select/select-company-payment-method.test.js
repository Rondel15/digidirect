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

    describe('Magento_CompanyPayment/js/form/element/select/select-company-payment-method', function () {
        var availablePaymentsField,
            params,
            testObj,
            injector = new Squire();

        beforeEach(function (done) {
            params = {
                dataScope: '',
                availablePaymentsFieldName: 'testAvailablePaymentsField',
                applicablePaymentMethods: {
                    b2b: '0',
                    allEnabled: '1'
                },
                b2bPaymentMethods: 'Method 1,Method 3',
                options: [
                    {label: 'B2B', value: '0'},
                    {label: 'All', value: '1'},
                    {label: 'Selected', value: '2'}
                ],
                value: '0'
            };

            injector.require([
                'Magento_CompanyPayment/js/form/element/select/select-company-payment-method',
                'uiRegistry'
            ], function (SelectPayment, registry) {
                availablePaymentsField = {
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
                registry.set('testAvailablePaymentsField', availablePaymentsField);
                testObj = new SelectPayment(params);
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

            it('Check _selectAvailablePaymentsFieldOptions call on initialize', function () {
                spyOn(testObj, '_selectAvailablePaymentsFieldOptions');
                testObj.initialize();
                expect(testObj._selectAvailablePaymentsFieldOptions).toHaveBeenCalled();
            });
        });

        describe('"onUpdate" method', function () {
            it('Check for defined', function () {
                expect(testObj.onUpdate).toBeDefined();
                expect(testObj.onUpdate).toEqual(jasmine.any(Function));
            });

            it('Check _selectAvailablePaymentsFieldOptions method call into onUpdate', function () {
                spyOn(testObj, '_selectAvailablePaymentsFieldOptions');
                testObj.onUpdate();
                expect(testObj._selectAvailablePaymentsFieldOptions).toHaveBeenCalled();
            });
        });

        describe('Change availablePaymentsField state', function () {
            it('self.disabled = false, self.value = applicablePaymentMethods.b2b', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('0');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().set).toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = false, self.value = applicablePaymentMethods.allEnabled', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('1');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().set).toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = false, self.value = "specific"', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(false);
                spyOn(testObj, 'value').and.returnValue('2');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(false);
                expect(testObj.availablePaymentsField().set)
                    .toHaveBeenCalledWith('disabled', false, jasmine.anything());
            });

            it('self.disabled = true, self.value = applicablePaymentMethods.b2b', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('0');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().set).toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = true, self.value = applicablePaymentMethods.allEnabled', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('1');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().set).toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });

            it('self.disabled = true, self.value = "specific"', function () {
                spyOn(testObj, 'availablePaymentsFieldState').and.callThrough();
                spyOn(testObj, 'disabled').and.returnValue(true);
                spyOn(testObj, 'value').and.returnValue('2');
                testObj._updateAvailablePaymentsFieldState();
                expect(testObj.availablePaymentsFieldState).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().set).toHaveBeenCalledWith('disabled', true, jasmine.anything());
            });
        });

        describe('"_getInitialAvailablePaymentFieldOptions" method', function () {
            it('Check for defined', function () {
                expect(testObj._getInitialAvailablePaymentFieldOptions).toBeDefined();
                expect(testObj._getInitialAvailablePaymentFieldOptions).toEqual(jasmine.any(Function));
            });

            it('Check get value', function () {
                expect(testObj._getInitialAvailablePaymentFieldOptions()).toEqual(['1', '2', '3']);
            });
        });

        describe('"_getSelectedPaymentMethods" method', function () {
            it('Check for defined', function () {
                expect(testObj._getSelectedPaymentMethods).toBeDefined();
                expect(testObj._getSelectedPaymentMethods).toEqual(jasmine.any(Function));
            });

            it('Check get value', function () {
                expect(testObj._getSelectedPaymentMethods()).toEqual(['Method 1', 'Method 3']);
            });
        });

        describe('"_selectAvailablePaymentsFieldOptions" method', function () {
            it('Check for defined', function () {
                expect(testObj._selectAvailablePaymentsFieldOptions).toBeDefined();
                expect(testObj._selectAvailablePaymentsFieldOptions).toEqual(jasmine.any(Function));
            });

            it('Select "B2B" methods', function () {
                spyOn(testObj.availablePaymentsField(), 'disabled');
                spyOn(testObj.availablePaymentsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('0');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailablePaymentsFieldOptions();
                expect(testObj.availablePaymentsField().disabled).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().value).toHaveBeenCalledWith(['Method 1', 'Method 3']);
            });

            it('Select "All" methods', function () {
                spyOn(testObj.availablePaymentsField(), 'disabled');
                spyOn(testObj.availablePaymentsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('1');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailablePaymentsFieldOptions();
                expect(testObj.availablePaymentsField().disabled).toHaveBeenCalledWith(true);
                expect(testObj.availablePaymentsField().value).toHaveBeenCalledWith([ '1', '2', '3' ]);
            });

            it('Select "Specific" methods', function () {
                spyOn(testObj.availablePaymentsField(), 'disabled');
                spyOn(testObj.availablePaymentsField(), 'value');

                spyOn(testObj, 'value').and.returnValue('2');
                spyOn(testObj, 'disabled').and.returnValue(false);

                testObj._selectAvailablePaymentsFieldOptions();
                expect(testObj.availablePaymentsField().disabled).toHaveBeenCalledWith(false);
                expect(testObj.availablePaymentsField().value).toHaveBeenCalledWith([]);
            });
        });
    });
});
