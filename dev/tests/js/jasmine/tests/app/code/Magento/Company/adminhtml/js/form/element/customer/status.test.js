/**
 *  ADOBE CONFIDENTIAL
 *
 *  Copyright 2024 Adobe
 *  All Rights Reserved.
 *
 *  NOTICE: All information contained herein is, and remains
 *  the property of Adobe and its suppliers, if any. The intellectual
 *  and technical concepts contained herein are proprietary to Adobe
 *  and its suppliers and are protected by all applicable intellectual
 *  property laws, including trade secret and copyright laws.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from Adobe.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire',
    'underscore'
], function (Squire, _) {
    'use strict';

    describe('Magento_Company/js/form/element/customer/status', function () {
        let StatusComponent,
            customerFormDataMock = {},
            customerFormDataSourceMock = {
                on: jasmine.createSpy(),
                set: jasmine.createSpy(),
                get: function (property) {
                    return customerFormDataMock[property];
                }
            },
            defaultConfig = {
                provider: 'customerFormDataSourceMock',
                dataScope: 'customer.company.status',
                default: 1,
                valueMap: {
                    'false': 0,
                    'true': 1
                }
            },
            injector = new Squire(),
            obj;

        beforeEach(function (done) {
            injector.require([
                'Magento_Company/js/form/element/customer/status',
                'uiRegistry',
                'knockoutjs/knockout-es5'
                // eslint-disable-next-line max-nested-callbacks
            ], function (Status, registry) {
                registry.set('customerFormDataSourceMock', customerFormDataSourceMock);
                StatusComponent = Status;
                done();
            });
        });

        afterEach(function () {
            injector.clean();
        });

        afterAll(function () {
            injector.remove();
        });

        describe('Initial value', function () {
            it('New Customer form default - Active', function () {
                let testConfig = {
                    multiCompany: false,
                    checked: false
                };

                // Should use default value
                // Ignore value from config
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.checked()).toBeTrue();
                expect(obj.disabled()).toBeFalse();
            });

            it('Existing Customer - Single Company - Not Active', function () {
                customerFormDataMock = {'customer.company.status': 0};
                let testConfig = {
                    multiCompany: false,
                    checked: true
                };

                // Should use form data value in case of single-assignment.
                // Ignore value from config
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.checked()).toBeFalse();
                expect(obj.disabled()).toBeFalse();
            });

            it('Existing Customer - Single Company - Active', function () {
                customerFormDataMock = {'customer.company.status': 1};
                let testConfig = {
                    multiCompany: false,
                    checked: false
                };

                // Should use form data value in case of single-assignment.
                // Ignore value from config
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.checked()).toBeTrue();
                expect(obj.disabled()).toBeFalse();
            });

            it('Customer is assigned to Multiple Companies - Active', function () {
                customerFormDataMock = {'customer.company.status': 0};

                let testConfig = {
                    multiCompany: true,
                    checked: true,
                    disabled: true
                };

                // Should ignore form data value in case of multi-assignment.
                // Use value from config
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.checked()).toBeTrue();
                expect(obj.disabled()).toBeTrue();
            });

            it('Customer is assigned to Multiple Companies - Not Active', function () {
                customerFormDataMock = {'customer.company.status': 1};
                let testConfig = {
                    multiCompany: true,
                    checked: false,
                    disabled: true
                };

                // Should ignore form data value in case of multi-assignment.
                // Use value from config
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.checked()).toBeFalse();
                expect(obj.disabled()).toBeTrue();
            });
        });

        describe('Element Notice', function () {
            let testConfig = {
                    superuser_config: {
                        notice: 'The user <%- username %> is the company admin'
                    },
                    paths: {
                        'is_super_user': 'customer.company.is_super_user',
                        'company_name': 'customer.company.company_name',
                        'first_name': 'customer.firstname',
                        'last_name': 'customer.lastname'
                    }
                },
                testData = {
                    'customer.company.company_name': 'Test Company A',
                    'customer.firstname': 'John',
                    'customer.lastname': 'Doe'
                };

            it('New Customer form default - No Notice', function () {
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.notice()).toEqual('');
                expect(obj.disabled()).toBeFalse();
            });

            it('Single Company - Regular User - No Notice', function () {
                customerFormDataMock = _.extend({}, testData, {
                    'customer.company.is_super_user': 0
                });

                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.notice()).toEqual('');
                expect(obj.disabled()).toBeFalse();
            });

            it('Single Company - Super User - Admin Notice', function () {
                customerFormDataMock = _.extend({}, testData, {
                    'customer.company.is_super_user': 1
                });
                testConfig = _.extend({}, testConfig, {
                    notice: 'Status cannot be changed'
                });
                // Should override element notice to 'Company Admin' notice
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.notice).toEqual('The user John Doe is the company admin');
                expect(obj.disabled()).toBeTrue();
            });

            it('Multi Company - Super User - Notice', function () {
                customerFormDataMock = _.extend({}, testData, {
                    'customer.company.is_super_user': 1
                });
                // Simulate configuration provided from backend
                testConfig = _.extend({}, testConfig, {
                    multiCompany: true,
                    disabled: true,
                    notice: 'Status cannot be changed'
                });
                // Should show element notice
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.notice()).toEqual('Status cannot be changed');
                expect(obj.disabled()).toBeTrue();
            });

            it('Multi Company - Regular User - Notice', function () {
                customerFormDataMock = _.extend({}, testData, {
                    'customer.company.is_super_user': 0
                });
                // Simulate configuration provided from backend
                testConfig = _.extend({}, testConfig, {
                    multiCompany: true,
                    disabled: true,
                    notice: 'Status cannot be changed'
                });
                // Should show element notice
                obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                expect(obj.notice()).toEqual('Status cannot be changed');
                expect(obj.disabled()).toBeTrue();
            });
        });

        describe('Change Company Selection', function () {
            let activeData = [
                    {
                        selected: [1],
                        checked: true,
                        disabled: false
                    },
                    {
                        selected: [1, 2],
                        checked: true,
                        disabled: true
                    },
                    {
                        selected: [1, 2, 3],
                        checked: true,
                        disabled: true
                    }
                ],
                inactiveData = [
                    {
                        selected: [1],
                        checked: false,
                        disabled: false
                    },
                    {
                        selected: [1, 2],
                        checked: false,
                        disabled: true
                    },
                    {
                        selected: [1, 2, 3],
                        checked: false,
                        disabled: true
                    }
                ];

            describe('New Customer', function () {
                activeData.forEach(function (data) {
                    it('Status: Active. Companies Selected: ' + data.selected.length, function () {
                        let testConfig = {
                            multiCompany: false,
                            checked: true
                        };

                        obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                        expect(obj.checked()).toBeTrue();
                        expect(obj.disabled()).toBeFalse();

                        obj.setStatusSelectorState(data.selected);
                        expect(obj.checked()).toEqual(data.checked);
                        expect(obj.disabled()).toEqual(data.disabled);
                    });
                });
            });

            describe('Company Customer', function () {
                activeData.forEach(function (data) {
                    it('Status: Active. Companies Selected: ' + data.selected.length, function () {
                        customerFormDataMock = {'customer.company.status': 1};
                        let testConfig = {
                            multiCompany: false,
                            checked: true
                        };

                        obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                        expect(obj.checked()).toBeTrue();
                        expect(obj.disabled()).toBeFalse();

                        obj.setStatusSelectorState(data.selected);
                        expect(obj.checked()).toEqual(data.checked);
                        expect(obj.disabled()).toEqual(data.disabled);
                    });
                });

                inactiveData.forEach(function (data) {
                    it('Status: Inactive. Companies Selected: ' + data.selected.length, function () {
                        customerFormDataMock = {'customer.company.status': 0};
                        let testConfig = {
                            multiCompany: false,
                            checked: false
                        };

                        obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                        expect(obj.checked()).toBeFalse();
                        expect(obj.disabled()).toBeFalse();

                        obj.setStatusSelectorState(data.selected);
                        expect(obj.checked()).toEqual(data.checked);
                        expect(obj.disabled()).toEqual(data.disabled);
                    });
                });
            });

            describe('Multi-Company Customer', function () {
                activeData.forEach(function (data) {
                    it('Status: Active. Companies Selected: ' + data.selected.length, function () {
                        customerFormDataMock = {'customer.company.status': 1};
                        let testConfig = {
                            multiCompany: true,
                            checked: true,
                            disabled: true
                        };

                        obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                        expect(obj.checked()).toBeTrue();
                        expect(obj.disabled()).toBeTrue();

                        obj.setStatusSelectorState(data.selected);
                        expect(obj.checked()).toEqual(data.checked);
                        expect(obj.disabled()).toEqual(data.disabled);
                    });
                });

                inactiveData.forEach(function (data) {
                    it('Status: Inactive. Companies Selected: ' + data.selected.length, function () {
                        customerFormDataMock = {'customer.company.status': 0};
                        let testConfig = {
                            multiCompany: true,
                            checked: false,
                            disabled: true
                        };

                        obj = new StatusComponent(_.extend({}, defaultConfig, testConfig));
                        expect(obj.checked()).toBeFalse();
                        expect(obj.disabled()).toBeTrue();

                        obj.setStatusSelectorState(data.selected);
                        expect(obj.checked()).toEqual(data.checked);
                        expect(obj.disabled()).toEqual(data.disabled);
                    });
                });
            });
        });
    });
});
