define([
    'Digidirect_Utilities/js/form/element/ajax-auto-complete',
    'mage/template'
], function (AjaxElement) {
    'use strict';

    return AjaxElement.extend({

        /**
         *
         * @param data
         * @returns {exports}
         */
        setAjaxAdditionalData: function (data) {
            this.additionalData['attribute_set_id'] = data['attribute_set_id'];

            return this._super();
        }
    });
});
