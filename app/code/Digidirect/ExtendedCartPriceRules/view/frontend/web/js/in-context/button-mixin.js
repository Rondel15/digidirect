define([
    'jquery'
], function ($) {
    'use strict';

    var mixin = {
        click: function (event) {
            if ($('a[data-action="' + this.linkDataAction + '"]').hasClass('disabled')) {
                event.preventDefault();
            } else {
                this._super(event);
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
