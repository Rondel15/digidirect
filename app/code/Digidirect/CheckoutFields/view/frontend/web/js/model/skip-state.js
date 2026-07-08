define([
    'ko'
], function (ko) {
    'use strict';

    return {
        checkoutSkipCustomValidation: ko.observable(null),
        setSkipValidation: function (flag) {
            this.checkoutSkipCustomValidation(flag);
        },
        getSkipValidation: function () {
            return this.checkoutSkipCustomValidation();
        }
    };
});
