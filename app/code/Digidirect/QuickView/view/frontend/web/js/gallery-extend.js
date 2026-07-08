define(['jquery'], function ($) {
    'use strict';

    return function (target) {
        return target.extend({
            initialize: function (config, element) {
                this._super(config, element);

                if (this.isTouchEnabled) {
                    this.offMouseUpEvent(element);
                }
            },

            /**
             * Off mouseup event for touch devices
             * @param element
             */
            offMouseUpEvent: function (element) {
                $(element).off('mouseup', '.fotorama__stage__frame');
            }
        })
    }

});