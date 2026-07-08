/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2019 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

define([
    'ko',
    'uiComponent',
    'jquery',
    'mage/translate'
], function (ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Plumrocket_Newsletterpopup/integration/custom-fields'
        },

        customFields: ko.observable(false),

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (typeof window.prnewsletterpopup === 'undefined') {
                window.prnewsletterpopup = {loadCustomFields: {}};
            }

            window.prnewsletterpopup.loadCustomFields.constantcontact = this.loadCustomFields.bind(this);
        },

        loadCustomFields: function (url) {
            var self = this;

            $.ajax({
                url: url,
                data: {},
                method: 'GET',
                showLoader: true,
                dataType: 'json',
                success: function (response) {
                    if ('success' !== response.result) {
                        return false;
                    }

                    if (0 !== response.info.length) {
                        self.customFields(response.info);
                    } else {
                        self.customFields([]);
                    }
                },
                error: function (response) {
                    self.showErrorMessage(__('Something went wrong.') + '' + response.status + ':' + response.statusText);
                }
            });
        }
    });
});
