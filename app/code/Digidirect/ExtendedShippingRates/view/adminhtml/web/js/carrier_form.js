define([
    'jquery',
    'Magento_Ui/js/form/form',
    'uiRegistry',
    'mageUtils',
    'mage/validation/url'
], function ($, form, uiRegistry, utils, mageValidationUrl) {
    'use strict';

    return form.extend({

        addMethod: function (method, params) {
            mageValidationUrl.redirect(params.redirectUrl);
        }
    });
});
