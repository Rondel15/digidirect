define(['jquery'], function ($) {
    'use strict';
    return function (target) {
        return target.extend({
            showFormPopUp: function () {
                $(document).trigger('av_address_form_loaded');

                return this._super();
            }
        });
    };
});
