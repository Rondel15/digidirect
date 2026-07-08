define([
    'jquery',
    'domReady!'
], function ($) {
    var freegiftForm = {
        update: function () {
            this.initActions();

            var actionFieldset = $('fieldset[id*="actions_fieldset"]');
            actionFieldset.show();

            var action = $('select[name="simple_action"]').val();
            if (action === undefined) {
                return false;
            }

            switch (action) {
                case 'freegift_cart':
                case 'freegift_spent':
                    actionFieldset.hide();
                    break;
            }
        },

        initActions: function () {
            var actions_fieldset = $('fieldset[id*="actions_fieldset"]');
            if (actions_fieldset.length) {
                window.sales_rule_form = window[actions_fieldset.attr('id')];
            }
        }
    };

    setTimeout(function(){
        freegiftForm.update();
    }, 2000);

    return freegiftForm;
});
