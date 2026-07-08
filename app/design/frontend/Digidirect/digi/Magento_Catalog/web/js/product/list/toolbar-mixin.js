define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.productListToolbarForm', widget, {
            _bind: function (elements, paramName, defaultValue) {
                var self = this;
                elements.each(function(index, element) {
                    if ($(element).is('select')) {
                        $(element).on('change', {
                            paramName: paramName,
                            'default': defaultValue
                        }, $.proxy(self._processSelect, self));
                    } else {
                        $(element).on('click', {
                            paramName: paramName,
                            'default': defaultValue
                        }, $.proxy(self._processLink, self));      
                    }        
                })

                        
            }
        });
        return $.mage.productListToolbarForm;
    };
});