/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_CompanyRelation/js/grid/columns/multiselect'
], function (Multiselect) {
    'use strict';

    describe('Magento_CompanyRelation/js/grid/columns/multiselect', function () {
        let obj;

        beforeEach(function () {
            obj = Multiselect({
                rows: [
                    {
                        entity_id: 1,
                        name: 'Record 1'
                    },
                    {
                        entity_id: 2,
                        name: 'Record 1'
                    },
                    {
                        entity_id: 3,
                        name: 'Record 1'
                    }
                ],
                indexField: 'entity_id',
                totalRecords: 20
            });
            obj.selected.push(1);
        });

        it('Hides "Select All" option', function () {
            expect(obj.isActionRelevant('selectAll')).toBeFalse();
        });

        it('Shows "Select All on This Page" option', function () {
            expect(obj.isActionRelevant('selectPage')).toBeTrue();
        });

        it('Shows "Select All on This Page" option with all items on the page', function () {
            obj.totalRecords(obj.rows.length);
            expect(obj.isActionRelevant('selectPage')).toBeTrue();
        });

        it('Shows "Deselect All on This Page" option', function () {
            obj.selected.push(1);
            expect(obj.isActionRelevant('deselectPage')).toBeTrue();
        });

        it('Shows "Deselect All" option', function () {
            obj.selected.push(1);
            expect(obj.isActionRelevant('deselectAll')).toBeTrue();
        });

        it('Does not show "Select All on This Page" option with all items selected on the page', function () {
            obj.selected.push(1);
            obj.selected.push(2);
            obj.selected.push(3);
            expect(obj.isActionRelevant('selectPage')).toBeFalse();
        });
    });
});
