/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'squire',
    'jquery',
    'ko'
], function (Squire, $, ko) {
    'use strict';

    describe('Magento_CompanyRelation/js/modal/assign-companies', function () {
        let testCompanyName = 'Company A',
            testCompanyId = 321,
            titleTmpl = 'Title template %1',
            companyFormDataSourceMock,
            regularCompanyDataSourceMock,
            companyHierarchyDataSourceMock,
            selectProviderMock,
            messagesProviderMock,
            injector = new Squire(),
            defered,
            mocks = {
                'Magento_CompanyRelation/js/action/assign': jasmine.createSpy().and.callFake(function () {
                    return defered;
                }),
                'Magento_Ui/js/modal/confirm': jasmine.createSpy()
            },
            obj;

        beforeEach(function (done) {
            injector.mock(mocks);
            injector.require([
                'Magento_CompanyRelation/js/modal/assign-companies',
                'uiRegistry',
                'knockoutjs/knockout-es5'
            // eslint-disable-next-line max-nested-callbacks
            ], function (AssignCompaniesModal, registry) {
                companyFormDataSourceMock = {
                    on: jasmine.createSpy(),
                    // eslint-disable-next-line max-nested-callbacks
                    get: function (property) {
                        let data = {
                            'data.general.company_name': testCompanyName,
                            'data.id': testCompanyId
                        };

                        return data[property];
                    }
                };
                regularCompanyDataSourceMock = {
                    selections: jasmine.createSpy().and.returnValue({selected: ko.observable}),
                    reload: jasmine.createSpy()
                };
                companyHierarchyDataSourceMock = {
                    reload: jasmine.createSpy()
                };
                selectProviderMock = {
                    on: jasmine.createSpy(),
                    rows: function () {return [
                        {
                            entity_id: 3,
                            company_name: 'Company 3'
                        },
                        {
                            entity_id: 5,
                            company_name: 'Company 5'
                        },
                        {
                            entity_id: 8,
                            company_name: 'Company 8'
                        }
                    ];}
                };
                messagesProviderMock = {
                    removeAll: jasmine.createSpy(),
                    add: jasmine.createSpy()
                };
                registry.set('companyFormDataSource', companyFormDataSourceMock);
                registry.set('regularCompanyDataSource', regularCompanyDataSourceMock);
                registry.set('companyHierarchyDataSource', companyHierarchyDataSourceMock);
                registry.set('selectProvider', selectProviderMock);
                registry.set('messagesProvider', messagesProviderMock);
                obj = new AssignCompaniesModal({
                    provider: 'companyFormDataSource',
                    regularCompanyListingProvider: 'regularCompanyDataSource',
                    companyHierarchyListingProvider: 'companyHierarchyDataSource',
                    messagesProvider: 'messagesProvider',
                    name: 'assign_child_companies_modal',
                    titleTmpl: titleTmpl,
                    actionBtnSelector: '.action-primary',
                    selectProvider: 'selectProvider',
                    actionUrl: 'https://commerce.loc/assign/endpoint'
                });
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
            it('Check title is set', function () {
                expect(typeof obj.options.title).toBe('string');
                expect(obj.options.title).toEqual('Title template Company A');
            });
        });

        describe('"setActionBtnState" method', function () {
            let container;

            beforeEach(function () {
               container = $('<div class="assign_child_companies_modal">' +
                   '<button class="action-primary">Action</button>' +
                   '</div>');
                container.appendTo(document.body);
            });

            afterEach(function () {
               container.remove();
            });

            it('Make action button inactive when selection is empty', function () {
                obj.setActionBtnState([]);
                expect($('.action-primary').is(':disabled')).toBe(true);
            });

            it('Make action button active when selection is not empty', function () {
                obj.setActionBtnState([3, 2, 1]);
                expect($('.action-primary').is(':disabled')).toBe(false);
            });
        });

        describe('"_reset" method', function () {
            let selected = [{company_name: 'Company 1'}];

            beforeEach(function () {
                obj.selectedItems = selected;
            });

            it('Do not update data if flag is not set', function () {
                obj.reload = false;
                obj._reset();
                expect(obj.selectedItems).toBe(selected);
                expect(regularCompanyDataSourceMock.selections).not.toHaveBeenCalled();
                expect(regularCompanyDataSourceMock.reload).not.toHaveBeenCalled();
            });

            it('Update data if flag is set', function () {
                obj.reload = true;
                obj._reset();
                expect(obj.selectedItems).toEqual([]);
                expect(regularCompanyDataSourceMock.selections).toHaveBeenCalled();
                expect(regularCompanyDataSourceMock.reload).toHaveBeenCalled();
            });
        });

        describe('"updateSelectedItemsData" method', function () {
            it('Stores selected entities data', function () {
                let expectedData = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];

                expect(JSON.stringify(obj.selectedItems)).toEqual(JSON.stringify([]));
                obj.updateSelectedItemsData([3, 5]);
                expect(JSON.stringify(obj.selectedItems)).toEqual(JSON.stringify(expectedData));
            });

            it('Remove deselected entities', function () {
                let storedData = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];

                obj.selectedItems = storedData;
                expect(obj.selectedItems).toEqual(storedData);
                obj.updateSelectedItemsData([]);
                expect(obj.selectedItems).toEqual([]);
            });

            it('Update selected entities', function () {
                let storedData = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];

                obj.selectedItems = storedData;
                expect(obj.selectedItems).toEqual(storedData);
                obj.updateSelectedItemsData([8]);
                expect(obj.selectedItems).toEqual([{
                    entity_id: 8,
                    company_name: 'Company 8'
                }]);
            });
        });

        describe('"doAction" method', function () {
            it('Call Action with required data', function () {
                let storedData = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];

                obj.selectedItems = storedData;
                defered = $.Deferred();
                obj.doAction();
                expect(mocks['Magento_CompanyRelation/js/action/assign']).toHaveBeenCalledWith(
                    'https://commerce.loc/assign/endpoint',
                    {
                        parent_id: 321,
                        company_ids: [3, 5]
                    }
                );
            });

            it('Action success', function () {
                spyOn(obj, 'closeModal');
                obj.selectedItems = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];
                expect(obj.reload).toEqual(false);

                defered = $.Deferred().resolve([{type: 'success', text: 'Success message.'}]);

                obj.doAction();
                expect(mocks['Magento_CompanyRelation/js/action/assign']).toHaveBeenCalledWith(
                    'https://commerce.loc/assign/endpoint',
                    {
                        parent_id: 321,
                        company_ids: [3, 5]
                    }
                );
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith('success', 'Success message.');
                expect(companyHierarchyDataSourceMock.reload).toHaveBeenCalled();
                expect(obj.closeModal).toHaveBeenCalled();
                expect(obj.reload).toEqual(true);
            });

            it('Action error', function () {
                spyOn(obj, 'closeModal');
                obj.selectedItems = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];
                expect(obj.reload).toEqual(false);

                defered = $.Deferred().reject([{type: 'error', text: 'Error message.'}]);

                obj.doAction();
                expect(mocks['Magento_CompanyRelation/js/action/assign']).toHaveBeenCalledWith(
                    'https://commerce.loc/assign/endpoint',
                    {
                        parent_id: 321,
                        company_ids: [3, 5]
                    }
                );
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith('error', 'Error message.');
                expect(companyHierarchyDataSourceMock.reload).toHaveBeenCalled();
                expect(obj.closeModal).toHaveBeenCalled();
                expect(obj.reload).toEqual(true);
            });

            it('Action both', function () {
                spyOn(obj, 'closeModal');
                obj.selectedItems = [
                    {
                        entity_id: 3,
                        company_name: 'Company 3'
                    },
                    {
                        entity_id: 5,
                        company_name: 'Company 5'
                    }
                ];
                expect(obj.reload).toEqual(false);

                defered = $.Deferred().reject([
                    {type: 'error', text: 'Error message.'},
                    {type: 'success', text: 'Success message.'}
                ]);

                obj.doAction();
                expect(mocks['Magento_CompanyRelation/js/action/assign']).toHaveBeenCalledWith(
                    'https://commerce.loc/assign/endpoint',
                    {
                        parent_id: 321,
                        company_ids: [3, 5]
                    }
                );
                expect(messagesProviderMock.removeAll).toHaveBeenCalled();
                expect(messagesProviderMock.add).toHaveBeenCalledWith('error', 'Error message.');
                expect(messagesProviderMock.add).toHaveBeenCalledWith('success', 'Success message.');
                expect(companyHierarchyDataSourceMock.reload).toHaveBeenCalled();
                expect(obj.closeModal).toHaveBeenCalled();
                expect(obj.reload).toEqual(true);
            });
        });

        describe('"assignSelected" method', function () {
            it('Show confirmation', function () {
                obj.assignSelected();
                expect(mocks['Magento_Ui/js/modal/confirm']).toHaveBeenCalled();
            });
        });
    });
});
