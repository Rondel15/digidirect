/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
*/

define([
    'underscore',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column',
    'jquery'
], function (_, $t, Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'Plumrocket_Newsletterpopup/grid/cells/radioselect',
            draggable: false,
            sortable: false,
            selectedVariableCode: null,
            selectedVariableType: null
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(['selectedVariableCode']);

            return this;
        },

        /**
         * Remove disable class from Insert Variable button after Variable has been chosen.
         *
         * @return {Boolean}
         */
        selectVariable: function () {
            var button = $('#insert_variable');
            if (button.hasClass('disabled')) {
                button.removeClass('disabled');
            }

            return true;
        }
    });
});
