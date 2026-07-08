/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2020 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
*/

require([
    'jquery',
    'mage/translate',
    'uiRegistry',
    'mage/apply/main',
    'mageUtils',
    'Magento_Ui/js/lib/spinner',
    'mage/adminhtml/tools'
], function ($, $t, registry, mageApply, utils, loader) {
    'use strict';

    window.PlumrocketVariables = {
        textareaElement: null,
        dialogWindowId: 'variables-chooser',
        insertFunction: 'PlumrocketVariables.insertVariable',

        init: function (textareaId) {
            this.textareaElement = $(textareaId);

        },

        /**
         * Init ui component grid on the form
         *
         * @return void
         */
        initUiGrid: function () {
            mageApply.apply();
            $('#' + this.dialogWindowId).applyBindings();
            loader.get('prnewsletterpopup_variables_listing.prnewsletterpopup_variables_listing.prnewsletterpopup_variables_columns').hide();
        },

        /**
         * Open slideout dialog window.
         *
         * @param {*} variablesContent
         */
        openDialogWindow: function (variablesContent) {
            var html = utils.copy(variablesContent),
                self = this;

            $('<div id="' + this.dialogWindowId + '">' + html + '</div>').modal({
                title: $t('Insert Variable'),
                type: 'slide',
                buttons:[{
                    text: $t('Insert Variable'),
                    class: 'action-primary ' + 'disabled',
                    attr: {
                        'id': 'insert_variable'
                    },
                    click: function () {
                        self.insertVariable(self.getVariableCode());
                    },
                }],
                /**
                 * @param {$.Event} e
                 * @param {Object} modal
                 */
                closed: function (e, modal) {
                    modal.modal.remove();
                    registry.get(
                        'prnewsletterpopup_variables_listing.prnewsletterpopup_variables_listing.prnewsletterpopup_variables_columns.variable_selector',
                        function (radioSelect) {
                            radioSelect.selectedVariableCode('');
                        }
                    );
                    cmSyncChangesByTextarea('#template_code', window.codeEditor);
                }
            });

            $('#' + this.dialogWindowId).modal('openModal');
        },

        /**
         * Get selected variable directive.
         *
         * @returns {*}
         */
        getVariableCode: function () {
            return registry.get('prnewsletterpopup_variables_listing.prnewsletterpopup_variables_listing.prnewsletterpopup_variables_columns.variable_selector')
                .selectedVariableCode();
        },

        /**
         * @param {*} variable
         */
        insertVariable: function (variable) {
            var textareaElm, scrollPos;

            $('#' + this.dialogWindowId).modal('closeModal');
            textareaElm = this.textareaElement.get(0);

            if (textareaElm) {
                scrollPos = textareaElm.scrollTop;
                updateElementAtCursor(textareaElm, variable);
                textareaElm.focus();
                textareaElm.scrollTop = scrollPos;
                $(textareaElm).change();
                textareaElm = null;
            }
        },
    };

    window.PlumrocketVariablePlugin = {
        /**
         * Load variables chooser.
         *
         * @param {String} url
         * @param {String} textareaElementId
         *
         * @return {Object}
         */
        loadChooser: function (url, textareaElementId ) {
            new Ajax.Request(url, {
                parameters: {},
                onComplete: function (transport) {
                    PlumrocketVariables.init(textareaElementId);
                    PlumrocketVariables.openDialogWindow(transport.responseText);
                    PlumrocketVariables.initUiGrid();
                }.bind(this)
            });

            return this;
        },
    };
});
