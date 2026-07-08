define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function (_, uiRegistry, select) {
    'use strict';

    return select.extend({
        defaults: {
            showMsgActionFields: ['plp_label', 'pdp_description', 'url_promotion']
        },

        setInitialValue: function () {
            this._super();
            this.toggleActionTypeFields(this.value());
            return this;
        },

        onUpdate: function () {
            this._super();
            this.toggleActionTypeFields(this.value());
        },

        toggleActionTypeFields: function (val) {
            var self = this;

            if (this.showMsgActionFields.length) {
                if (val === 'show_msg') {
                    _.each(self.showMsgActionFields, function (item) {
                        uiRegistry.async(self.parentName + '.' + item)(function (field) {
                            if (field) {
                                field.show();
                            }
                        });
                    });
                } else {
                    _.each(self.showMsgActionFields, function (item) {
                        uiRegistry.async(self.parentName + '.' + item)(function (field) {
                            if (field) {
                                field.hide();
                            }
                        });
                    });
                }
            }
        }
    });
});
