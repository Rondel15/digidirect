/**
 * ADOBE CONFIDENTIAL
 *
 * Copyright 2023 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 */
/* eslint-disable max-nested-callbacks */
define([
    'Magento_CompanyShipping/js/form/element/use-config-settings'
], function (UseSettings) {
    'use strict';

    describe('Magento_CompanyShipping/js/form/element/use-config-settings', function () {
        var useSettingsObj;

        beforeEach(function () {
            useSettingsObj = new UseSettings({
                dataScope: '',
                default: '1'
            });
        });

        describe('"toggleShippingFieldDisabled" method', function () {
            it('Check for defined', function () {
                expect(useSettingsObj.toggleShippingFieldDisabled).toBeDefined();
                expect(useSettingsObj.toggleShippingFieldDisabled).toEqual(jasmine.any(Function));
            });
            it('Listens "disabled"', function () {
                spyOn(useSettingsObj, 'toggleShippingFieldDisabled');
                useSettingsObj.disabled(true);
                expect(useSettingsObj.toggleShippingFieldDisabled).toHaveBeenCalled();
            });
            it('Listens "checked"', function () {
                spyOn(useSettingsObj, 'toggleShippingFieldDisabled');
                useSettingsObj.checked(true);
                expect(useSettingsObj.toggleShippingFieldDisabled).toHaveBeenCalled();
            });
            it('Listens "value"', function () {
                spyOn(useSettingsObj, 'toggleShippingFieldDisabled');
                useSettingsObj.value(true);
                expect(useSettingsObj.toggleShippingFieldDisabled).toHaveBeenCalled();
            });
        });

        describe('it changes state', function () {
            it('checked = false, disabled = true', function () {
                spyOn(useSettingsObj, 'shippingFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(true);
                spyOn(useSettingsObj, 'checked').and.returnValue(false);

                useSettingsObj.toggleShippingFieldDisabled();
                expect(useSettingsObj.shippingFieldDisabled).toHaveBeenCalledWith(true);
            });
            it('checked = false, disabled = false', function () {
                spyOn(useSettingsObj, 'shippingFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(false);
                spyOn(useSettingsObj, 'checked').and.returnValue(false);

                useSettingsObj.toggleShippingFieldDisabled();
                expect(useSettingsObj.shippingFieldDisabled).toHaveBeenCalledWith(false);
            });
            it('checked = true, disabled = true', function () {
                spyOn(useSettingsObj, 'shippingFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(true);
                spyOn(useSettingsObj, 'checked').and.returnValue(true);

                useSettingsObj.toggleShippingFieldDisabled();
                expect(useSettingsObj.shippingFieldDisabled).toHaveBeenCalledWith(true);
            });
            it('checked = true, disabled = false', function () {
                spyOn(useSettingsObj, 'shippingFieldDisabled');
                spyOn(useSettingsObj, 'disabled').and.returnValue(false);
                spyOn(useSettingsObj, 'checked').and.returnValue(true);

                useSettingsObj.toggleShippingFieldDisabled();
                expect(useSettingsObj.shippingFieldDisabled).toHaveBeenCalledWith(true);
            });
        });
    });
});
