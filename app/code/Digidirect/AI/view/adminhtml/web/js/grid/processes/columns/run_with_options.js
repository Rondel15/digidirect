'use strict';

define([
    'Magento_Ui/js/grid/columns/column',
    'Digidirect_AI/js/grid/processes/modal/process_run'
], function (Column, runModal) {

    //extend ui component 'Column', update button, create custom event
    return Column.extend({
        getLabel: function (row) {
            return row[this.index] ? row[this.index]['label']: '';
        },
        preview: function (row) {

        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        },
        buttonPreview: function (row) {
            runModal.show(row);
        },
        getButtonHandler: function (row) {
            return this.buttonPreview.bind(this, row);
        }
    });
});