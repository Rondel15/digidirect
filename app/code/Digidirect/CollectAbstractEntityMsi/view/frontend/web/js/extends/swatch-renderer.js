define([
    'jquery',
    'underscore',
    'jquery/ui'
], function ($, _) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            options: {
                msiAvailability: '#msi-availability',
                msiAvailabilityImmediate: true
            },
            _OnClick: function ($this, $widget, eventName) {
                this._super($this, $widget, eventName);

                if (this.options.msiAvailabilityImmediate) {
                    $(this.options.msiAvailability).trigger('updateSwatches');
                } else {
                    var options = _.object(_.keys(this.optionsMap), {}),
                        result;

                    this.element.find('.' + this.options.classes.attributeClass + '[option-selected]').each(function () {
                        var attributeId = $(this).attr('attribute-id');

                        options[attributeId] = $(this).attr('option-selected');
                    });

                    result = _.findKey(this.options.jsonConfig.index, options);

                    if (typeof result !== 'undefined') {
                        $(this.options.msiAvailability).trigger('updateSwatches');
                    }
                }
            }
        });

        return $.mage.SwatchRenderer;
    };
});
