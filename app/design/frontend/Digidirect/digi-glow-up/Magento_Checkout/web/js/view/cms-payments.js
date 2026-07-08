define([
    'uiComponent'
], function (Component) {
    'use strict';

    var cmsPayments = window.cmsPayments;

    return Component.extend({
        cmsData: cmsPayments ? cmsPayments.content : '',
        isVisible: function () {
            return cmsPayments;
        }
    });
});
