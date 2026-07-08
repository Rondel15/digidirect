/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* eslint-disable max-nested-callbacks */
define([
    'squire'
], function (Squire) {
    'use strict';

    describe('Magento_RequisitionList/js/requisition', function () {
        var requisitionListComponent,
            mockRequisition,
            mockCustomerData,
            injector;

        beforeEach(function (done) {
            injector = new Squire();

            mockRequisition = {
                items: [],
                max_allowed_requisition_lists: 5,
                count: 0
            };

            mockCustomerData = {
                get: jasmine.createSpy('customerDataGet').and.returnValue(function () {
                    return mockRequisition;
                }),
                reload: jasmine.createSpy('customerDataReload'),
                getExpiredSectionNames: jasmine.createSpy('getExpiredSectionNames').and.returnValue([])
            };
            injector.mock('Magento_Customer/js/customer-data', mockCustomerData);

            injector.require(['Magento_RequisitionList/js/requisition'], function (RequisitionComponent) {
                requisitionListComponent = new RequisitionComponent();
                done();
            });
        });

        afterEach(function () {
            try {
                injector.clean();
                injector.remove();
            } catch (e) {}
        });

        describe('Initialization', function () {
            it('should call customerData.get with "requisition"', function () {
                expect(mockCustomerData.get).toHaveBeenCalledWith('requisition');
            });

            it('should call customerData.reload with "requisition"', function () {
                requisitionListComponent.initialize();
                expect(mockCustomerData.reload).toHaveBeenCalledWith(['requisition'], true);
            });
        });

        describe('isCanCreateList', function () {
            it('should return true when there are no items in the requisition', function () {
                expect(requisitionListComponent.isCanCreateList()).toBe(true);
            });

            it('should return true when items count is less than max_allowed_requisition_lists', function () {
                mockRequisition.items = [1, 2];
                mockRequisition.max_allowed_requisition_lists = 3;
                expect(requisitionListComponent.isCanCreateList()).toBe(true);
            });

            it('should return false when items count is equal to max_allowed_requisition_lists', function () {
                mockRequisition.items = [1, 2, 3, 4, 5];
                mockRequisition.max_allowed_requisition_lists = 5;
                expect(requisitionListComponent.isCanCreateList()).toBe(false);
            });

            it('should return false when items count is greater than max_allowed_requisition_lists', function () {
                mockRequisition.items = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];
                mockRequisition.max_allowed_requisition_lists = 5;
                expect(requisitionListComponent.isCanCreateList()).toBe(false);
            });
        });
    });
});

