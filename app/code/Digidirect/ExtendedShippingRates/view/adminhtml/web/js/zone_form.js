require([
    'jquery','Magento_Ui/js/lib/view/utils/async'
], function($) {
    'use strict';

    $.async('[name="attribute_set"]', function (el) {
        var tab = $('[data-index="conditions"]');
        if (tab !== 'undefined') {
            if ($(el).val() == 0) {
                tab.show()
            } else {
                tab.hide();
            }
        }
    });

    $(document).on('change', '[name="attribute_set"]', function () {
        var tab = $('[data-index="conditions"]');
        if ($(this).val() == 0) {
            tab.show();
        } else {
            tab.hide();
        }
    });
});