/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/* eslint-disable max-nested-callbacks */
define([
    'Magento_CompanyPayment/js/form/element/use-config-settings'
], function (UseSettings) {
    'use strict';

    describe('Magento_CompanyPayment/js/form/element/use-config-settings', function () {
        var useSettingsObj;

        beforeEach(function () {
            useSettingsObj = new UseSettings({
                dataScope: '',
                default: '1'
            });
        });

        describe('"togglePaymentsFieldDisabled" method', function () {
            it('Check for defined', function () {
                expect(useSettingsObj.togglePaymentsFieldDisabled).toBeDefined();
                expect(useSettingsObj.togglePaymentsFieldDisabled).toEqual(jasmine.any(Function));
            });
            it('Listens "disabled"', function () {
                spyOn(useSettingsObj, 'togglePaymentsFieldDisabled');
                useSettingsObj.disabled(true);
                expect(useSettingsObj.togglePaymentsFieldDisabled).toHaveBeenCalled();
            });
            it('Listens "checked"', function () {
                spyOn(useSettingsObj, 'togglePaymentsFieldDisabled');
                useSettingsObj.checked(true);
                expect(useSettingsObj.togglePaymentsFieldDisabled).toHaveBeenCalled();
            });
            it('Listens "value"', function () {
                spyOn(useSettingsObj, 'togglePaymentsFieldDisabled');
                useSettingsObj.value(true);
                expect(useSettingsObj.togglePaymentsFieldDisabled).toHaveBeenCalled();
            });
        });

        describe('it changes state', function () {
            it('checked = false, disabled = true', function () {
                spyOn(useSettingsObj, 'paymentsFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(true);
                spyOn(useSettingsObj, 'checked').and.returnValue(false);

                useSettingsObj.togglePaymentsFieldDisabled();
                expect(useSettingsObj.paymentsFieldDisabled).toHaveBeenCalledWith(true);
            });
            it('checked = false, disabled = false', function () {
                spyOn(useSettingsObj, 'paymentsFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(false);
                spyOn(useSettingsObj, 'checked').and.returnValue(false);

                useSettingsObj.togglePaymentsFieldDisabled();
                expect(useSettingsObj.paymentsFieldDisabled).toHaveBeenCalledWith(false);
            });
            it('checked = true, disabled = true', function () {
                spyOn(useSettingsObj, 'paymentsFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(true);
                spyOn(useSettingsObj, 'checked').and.returnValue(true);

                useSettingsObj.togglePaymentsFieldDisabled();
                expect(useSettingsObj.paymentsFieldDisabled).toHaveBeenCalledWith(true);
            });
            it('checked = true, disabled = false', function () {
                spyOn(useSettingsObj, 'paymentsFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(false);
                spyOn(useSettingsObj, 'checked').and.returnValue(true);

                useSettingsObj.togglePaymentsFieldDisabled();
                expect(useSettingsObj.paymentsFieldDisabled).toHaveBeenCalledWith(true);
            });
        });
    });
});
