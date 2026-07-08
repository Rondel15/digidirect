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

/* eslint max-nested-callbacks: 0 */

define([
    'squire',
    'underscore',
    'Magento_Company/js/form/element/customer/company-select'
], function (Squire, _, CompanySelect) {
    'use strict';

    describe('Magento_Company/js/form/element/customer/company-select', function () {
        let obj,
            injector = new Squire(),
            defaultConfig = {
                dataScope: 'customer.company.company-select',
                options: [{label: 'Company A',value: '1'}, {label: 'Company B',value: '2'}],
                total: 2
            };

        beforeEach(function () {
            obj = new CompanySelect(_.extend({}, defaultConfig));
        });

        afterEach(function () {
            injector.clean();
        });

        afterAll(function () {
            injector.remove();
        });

        describe('Test initialization', function () {
            it('initialize, loadOptions and success method should be defined', function () {
                expect(obj.initialize).toBeDefined();
                expect(obj.loadOptions).toBeDefined();
                expect(obj.success).toBeDefined();
            });
        });

        describe('Test loadOptions method', function () {
            it('hasData method should be call', function () {
                var searchKey = 'Company A';

                spyOn(obj, 'hasData');
                obj.loadOptions(searchKey);
                expect(obj.hasData).toHaveBeenCalled();
            });
        });

        describe('Test success method', function () {
            it('test with company response', function () {
                let response = {
                    options: [{label: 'Company C', value: '123', level:0}]
                };

                obj.success(response);
                expect(obj.cacheOptions.plain.length).toEqual(3);
                expect(obj.cacheOptions.plain[0].label).toEqual('Company A');
                expect(obj.cacheOptions.plain[1].label).toEqual('Company B');
                expect(obj.cacheOptions.plain[2].label).toEqual('Company C');
                expect(obj.cacheOptions.plain[2].value).toEqual('123');
            });

            it('test with blank company response', function () {
                let response = {
                    options: []
                };

                obj.success(response);
                expect(obj.cacheOptions.plain.length).toEqual(2);
                expect(obj.cacheOptions.plain[0].label).toEqual('Company A');
                expect(obj.cacheOptions.plain[1].label).toEqual('Company B');
            });
        });
    });
});
