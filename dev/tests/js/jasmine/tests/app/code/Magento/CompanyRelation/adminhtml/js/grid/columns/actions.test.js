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
    'squire',
    'mage/translate'
], function (Squire, $t) {
    'use strict';

    describe('Magento_CompanyRelation/js/grid/columns/actions', function () {
        let obj,
            jq,
            originalJQueryAjax,
            messagesProviderMock,
            injector = new Squire(),
            mocks = {
                'Magento_Ui/js/modal/confirm': jasmine.createSpy()
            };

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_CompanyRelation/js/grid/columns/actions',
                'uiRegistry',
                'jquery',
                'knockoutjs/knockout-es5'
            ], function (Actions, registry, $) {
                messagesProviderMock = {
                    removeAll: jasmine.createSpy(),
                    add: jasmine.createSpy()
                };
                registry.set('messagesProviderMock', messagesProviderMock);

                obj = new Actions({
                    messagesProvider: 'messagesProviderMock'
                });

                obj.rows = [{
                    parent_company_entity_id: 1,
                    company_name: 'child company',
                    parent_company_name: 'parent company'
                }];
                obj.source = jasmine.createSpy().and.returnValue({
                    reload: jasmine.createSpy()
                });
                originalJQueryAjax = $.ajax;
                jq = $;

                done();
            });
        });

        afterEach(function () {
            jq.ajax = originalJQueryAjax;
            injector.clean();
        });

        afterAll(function () {
            injector.remove();
        });

        describe('Test "unassignCompany" method', function () {
            it('Show confirmation', function () {
                let action = {rowIndex: 0};

                obj.unassignCompany('unassign', '2', action);
                expect(mocks['Magento_Ui/js/modal/confirm']).toHaveBeenCalled();
            });
        });

        describe('Test "doAction" method', function () {
            it('Check ajax is called with success', function () {
                let action = {rowIndex: 0};

                $.ajax = jasmine.createSpy().and.callFake(function () {
                    let d = $.Deferred();

                    d.resolve({success: true, message: 'Success Message'});

                    return d.promise();
                });

                obj.doAction('2', action);
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith('success', 'Success Message');
                expect(obj.source().reload).toHaveBeenCalledWith({refresh: true});
            });

            it('Check ajax is called with error', function () {
                let action = {rowIndex: 0};

                $.ajax = jasmine.createSpy().and.callFake(function () {
                    let d = $.Deferred();

                    d.resolve({error: true, message: 'Error Message'});

                    return d.promise();
                });

                obj.doAction('2', action);
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith('error', 'Error Message');
                expect(obj.source().reload).toHaveBeenCalledWith({refresh: true});
            });

            it('Check ajax is called and fails', function () {
                let action = {rowIndex: 0};

                $.ajax = jasmine.createSpy().and.callFake(function () {
                    let d = $.Deferred();

                    d.reject();

                    return d.promise();
                });

                obj.doAction('2', action);
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith(
                    'error',
                    $t('A technical problem on the server caused an error.' +
                        ' Please try again later, or contact Support if the problem persists.')
                );
                expect(obj.source().reload).toHaveBeenCalledWith({refresh: true});
            });
        });
    });
});
