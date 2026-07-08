define([
    'jquery',
    'Magento_Theme/js/view/messages'
], function ($, Messages) {
    'use strict';

    return Messages.extend({
        addExtraClassName: function (element) {
            var className = $(element).find('[data-message-classname]').data('message-classname');
            if (className) {
                $(element).closest('.message').addClass(className);
            }
        }
    });
});
