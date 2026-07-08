'use strict';

define([
    'Magento_Ui/js/grid/columns/column',
    'Digidirect_AI/js/grid/log/modal/loginfo',
    'mage/translate'
], function (Column, logModal, $trans) {

    //extend ui component 'Column', update button, create custom event
    return Column.extend({
        getLabel: function (row) {
            return $trans('See details');
        },
        preview: function (row) {

        },
        getFieldHandler: function (row) {
            return this.preview.bind(this, row);
        },
        buttonPreview: function (row) {
            logModal.show(row);
        },
        getButtonHandler: function (row) {
            return this.buttonPreview.bind(this, row);
        }
    });
});