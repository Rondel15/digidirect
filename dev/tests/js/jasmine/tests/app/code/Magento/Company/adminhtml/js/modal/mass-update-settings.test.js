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
    'jquery',
    'squire',
    'uiCollection'
], function ($, Squire, Element) {
    'use strict';

    describe('Magento_Company/js/modal/mass-update-settings', function () {
        let modal, utils,
            confirmSpy = jasmine.createSpy('confirm'),
            mocks = {
                'Magento_Ui/js/modal/confirm': confirmSpy
            },
            injector = new Squire();

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_Company/js/modal/mass-update-settings',
                'mageUtils',
                'knockoutjs/knockout-es5'
            ], function (MassUpdateSettings, mageUtils) {
                modal = new MassUpdateSettings({
                    name: 'mass_update_settings_modal'
                });
                utils = mageUtils;
                done();
            });
        });

        afterEach(function () {
            injector.clean();
        });

        afterAll(function () {
            injector.remove();
        });

        it('"openModal" initialize selections', function () {
            let actionDetails = {}, selectionsData = {
                excludeMode: false,
                selected: [],
                params: {
                    customParam: 'customValue'
                }
            };

            spyOn(modal, '_convertSelection').and.returnValue({});
            modal.openModal(actionDetails, selectionsData);
            expect(modal._convertSelection).toHaveBeenCalledWith(selectionsData);
            expect(modal.actionSelections).toBeDefined();
        });

        it('"closeModal" reset fields', function () {
            const delegateSpy = jasmine.createSpy('delegate').and.returnValue({});

            modal.fieldset = () => ({
                delegate: delegateSpy
            });

            modal.closeModal();
            expect(delegateSpy).toHaveBeenCalledWith('reset');
        });

        describe('"onContentUpdate" change Action button state', function () {
            let container;

            beforeEach(function () {
                container =
                    $('<div class="mass_update_settings_modal">' +
                    '<button class="action-primary">Action</button>' +
                    '</div>');
                container.appendTo(document.body);
            });

            afterEach(function () {
                container.remove();
            });

            it('Action Btn is enabled if at least one "Change" checkbox is checked', function () {
                const delegateSpy = jasmine.createSpy('delegate').and.returnValue([false, true, false]);

                modal.fieldset = () => ({
                    delegate: delegateSpy
                });

                modal.onContentUpdate();
                expect(delegateSpy).toHaveBeenCalledWith('changeChecked');
                expect($('.action-primary').is(':disabled')).toBe(false);
            });

            it('Action Btn is disabled if there is no "Change" checkbox checked', function () {
                const delegateSpy = jasmine.createSpy('delegate').and.returnValue([false, false, false]);

                modal.fieldset = () => ({
                    delegate: delegateSpy
                });

                modal.onContentUpdate();
                expect(delegateSpy).toHaveBeenCalledWith('changeChecked');
                expect($('.action-primary').is(':disabled')).toBe(true);
            });
        });

        describe('"_convertSelection" method', function () {
            it('select specific items', function () {
                let result = modal._convertSelection({
                    excluded: ['12', '13', '14', '15'],
                    selected: ['10', '11'],
                    total: 2,
                    showTotalRecords: true,
                    excludeMode: false,
                    params: {
                        filters: {
                            placeholder: true
                        },
                        search: '',
                        namespace: 'namespace_value'
                    }
                });

                expect(JSON.stringify(result)).toEqual(
                    JSON.stringify({
                        selected: ['10', '11'],
                        selectedCount: 2,
                        filters: { 'placeholder': true },
                        search: '',
                        namespace: 'namespace_value'
                    })
                );
            });

            it('"Select All"', function () {
                let result = modal._convertSelection({
                    excluded: [],
                    selected: ['10', '11', '12', '13', '14', '15'],
                    total: 70,
                    showTotalRecords: true,
                    excludeMode: true,
                    params: {
                        filters: {'placeholder': true},
                        search: '',
                        namespace: 'namespace_value'
                    }}
                );

                expect(JSON.stringify(result)).toEqual(
                    JSON.stringify({
                        excluded: false,
                        selectedCount: 70,
                        filters: {placeholder: true},
                        search: '', namespace: 'namespace_value'
                    })
                );
            });
        });

        describe('"_getEnabledElementsValue" method', function () {
            it('collect enabled fields data', function () {
                let modalData,
                    ElementMock = Element.extend({
                        value: function () { return this.name; }
                    }),
                    elems = [
                        new ElementMock({disabled: false, name: 'test_1'}),
                        new Element({disabled: false, name: 'container_1'}).elems([
                            new ElementMock({disabled: true, name: 'test_2_1'}),
                            new ElementMock({disabled: false, name: 'test_2_2'})
                        ]),
                        new ElementMock({disabled: true, name: 'test_3'}),
                        new ElementMock({disabled:  function () {return true;}, name: 'test_4'}),
                        new ElementMock({disabled:  function () {return false;}, name: 'test_5'})
                    ];

                modal.fieldset = () => ({
                    elems: () => elems
                });

                modalData = modal._getEnabledElementsValue();
                expect(modalData).toEqual({
                    test_1: 'test_1',
                    test_2_2: 'test_2_2',
                    test_5: 'test_5'
                });
            });
        });

        describe('"_prepareRequestData" method', function () {
            it('builds request data', function () {
                let result;

                spyOn(modal, '_getEnabledElementsValue').and.returnValue({
                    test_1: 'test_1_value',
                    test_2_2: 'test_2_2_value',
                    test_5: 'test_5_value'
                });
                result = modal._prepareRequestData({
                    request_field_1: 'test_1',
                    container: {
                        request_field_2: 'test_2_2'
                    }
                });
                expect(result).toEqual({
                    request_field_1: 'test_1_value',
                    container: {
                        request_field_2: 'test_2_2_value'
                    }
                });
            });
        });

        describe('"updateSettings" method', function () {

            it('Validate Modal fields', function () {
                const elems = [
                    new Element({name: 'test_1', validate: function () {return {valid: true};}}),
                    new Element({name: 'container_1'}).elems([
                        new Element({name: 'test_2_1', validate: function () {return {valid: false};}})
                    ])
                ];

                modal.fieldset = () => ({
                    elems: () => elems
                });

                spyOn(modal, '_prepareRequestData');
                modal.updateSettings();
                expect(modal._prepareRequestData).not.toHaveBeenCalled();
            });

            it('Show Confirmation', function () {
                const selectionsData = {
                        excludeMode: false,
                        selected: [],
                        params: {
                            customParam: 'customValue'
                        }
                    },
                    elems = [
                        new Element({name: 'test_1', validate: function () {return {valid: true};}}),
                        new Element({name: 'container_1'}).elems([
                            new Element({name: 'test_2_1', validate: function () {return {valid: true};}})
                        ])
                    ];

                spyOn(modal, '_convertSelection').and.returnValue({
                    selected: ['10', '11'],
                    selectedCount: 2,
                    filters: { 'placeholder': true },
                    search: '',
                    namespace: 'namespace_value'
                });
                spyOn(modal, '_prepareRequestData').and.returnValue({
                    request_field_1: 'test_1_value',
                    container: {
                        request_field_2: 'test_2_2_value'
                    }
                });
                modal.openModal({}, selectionsData);

                modal.fieldset = () => ({
                    elems: () => elems
                });

                modal.form = jasmine.createSpy('formRequestFields').and.returnValue({});

                modal.updateSettings();
                expect(modal._convertSelection).toHaveBeenCalled();
                expect(modal._prepareRequestData).toHaveBeenCalled();
                expect(modal.form).toHaveBeenCalled();
                expect(confirmSpy).toHaveBeenCalledWith(
                    jasmine.objectContaining({
                        title: 'Change company settings',
                        content: 'Applying these changes will overwrite settings for 2 companies.'
                            + ' Are you sure you want to proceed?'
                    })
                );
            });
        });

        describe('"submitData" method', function () {
            it('Submit data to configured Url', function () {
                let requestData = {
                    request_field_1: 'test_1_value',
                    container: {
                        request_field_2: 'test_2_2_value'
                    },
                    selected: ['10', '11'],
                    selectedCount: 2,
                    filters: {
                        placeholder: true
                    },
                    search: '',
                    namespace: 'namespace_value'
                };

                modal.form = () => ({
                    actionUrl: 'action/url',
                    ajaxSaveType: 'simple'
                });

                spyOn(utils, 'ajaxSubmit').and.returnValue($.Deferred());
                modal.submitData(requestData);
                expect(utils.ajaxSubmit).toHaveBeenCalledWith(
                    {
                        url: 'action/url',
                        data: requestData
                    },
                    {
                        ajaxSaveType: 'simple'
                    }
                );
            });
        });
    });
});
