define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (target) {
        $.widget('digidirect.quickView', target, {
            options: {
                modal: {
                    type: 'popup',
                    buttons: [],
                    modalClass: 'quickView-modal'
                }
            }
        });

        return $.digidirect.quickView;
    };

});
