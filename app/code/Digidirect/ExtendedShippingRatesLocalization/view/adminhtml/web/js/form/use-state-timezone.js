define([
    'Magento_Ui/js/form/element/single-checkbox'
], function (Element) {
    'use strict';

    return Element.extend({

        defaults: {
            valuesForEnable: [],
            disabled: false,
            imports: {
                toggleDisable:
                    'digidirect_extendedshippingrates_quote_form.digidirect_extendedshippingrates_quote_form.general.zone_state_id:value'
            },
            listens: {
                disabled: 'updateValueForDisabledField'
            }
        },

        /**
         * {@inheritdoc}
         */
        initialize: function () {
            this._super();
            this.updateValueForDisabledField();
            if (!this.checked()) {
                this.disabled(true);
            }
            return this;
        },

        /**
         * Set element value to O(No) if element is invisible and disabled
         * Set element value to initialValue if element becomes visible and enable
         */
        updateValueForDisabledField: function () {
            if (this.disabled()) {
                this.set('value', 0);
            }
        },


        /**
         * Toggle disabled state.
         *
         * @param {String} selected
         */
        toggleDisable: function (selected) {
            this.disabled(!selected);
        }
    });
});
