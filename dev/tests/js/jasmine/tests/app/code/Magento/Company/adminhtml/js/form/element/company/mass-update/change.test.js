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

/*eslint max-nested-callbacks: 0*/
define([
    'Magento_Company/js/form/element/company/mass-update/change'
], function (ChangeCheckbox) {
    'use strict';

    describe('Magento_Company/js/form/element/company/mass-update/change', function () {
        var obj;

        beforeEach(function () {
            obj = new ChangeCheckbox({
                'dataScope': ''
            });
        });

        describe('"onCheckedChanged" method', function () {
            it('Check "disabledState" when checked', function () {
                obj.onCheckedChanged(true);
                expect(obj.disabledState()).toBeFalsy();
            });

            it('Check "disabledState" when unchecked', function () {
                obj.onCheckedChanged(false);
                expect(obj.disabledState()).toBeTruthy();
            });

            it('Check "changeChecked" when checked', function () {
                obj.onCheckedChanged(true);
                expect(obj.changeChecked()).toBeTruthy();
            });

            it('Check "changeChecked" when unchecked', function () {
                obj.onCheckedChanged(false);
                expect(obj.changeChecked()).toBeFalsy();
            });
        });
    });
});
