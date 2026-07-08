define([
    'Magento_Ui/js/form/element/abstract',
    'mage/template'
], function (CheckboxElement) {
    'use strict';

    return CheckboxElement.extend({
        defaults: {
            elementTmpl: 'Digidirect_Utilities/form/element/input',
            selectedEntity: '',
            selectedEntityAttribute: 'name',
            additionalData: {},
            entityIdAttribute: 'id',
            entityNameAttribute: 'name'
        },

        /**
         * @returns {Element}
         */
        initialize: function () {
            return this._super()
                .initStateConfig();
        },

        /**
         *
         * @param data
         * @returns {Element}
         */
        setAjaxAdditionalData: function (data) {
            return this;
        },

        /**
         * @returns {Element}
         */
        initStateConfig: function () {
            var entityAttrs;

            if (this.source) {
                entityAttrs = this.source.get(this.parentScope);
                this.selectedEntity = entityAttrs[this.selectedEntityAttribute];
                this.setAjaxAdditionalData(entityAttrs);
            }

            return this;
        }
    });
});
