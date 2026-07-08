define([
    'ko'
], function (ko) {
    'use strict';

    return {
        isNeedUpdate: ko.observable(null),
        clear: function () {
            this.isNeedUpdate(null);
        }
    };
});
