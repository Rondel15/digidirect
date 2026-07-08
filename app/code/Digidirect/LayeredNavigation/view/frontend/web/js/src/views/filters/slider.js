import $ from 'jquery';
import View from './../index';

export default class Link {
    constructor (options) {
        this.options = options;
        this.watchers(this.options);
    }

    watchers (options) {
        var self = this,
            elementID = options.sliderElement[0].id,
            $display = $('#' + elementID + '_display'),
            $slider = $('#' + elementID + '_slider');

        self.updateSliderData(options.from, options.to);
        $display.html(self.renderLabel(options.from) + ' - ' + self.renderLabel(options.to));

        $slider.slider({
            step: options.step,
            range: true,
            min: options.min,
            max: options.max,
            values: [options.from, options.to],
            slide: function (event, ui) {
                $display.html(self.renderLabel(ui.values[0]) + ' - ' + self.renderLabel(ui.values[1]));
                self.updateSliderData(ui.values[0], ui.values[1]);
            },
            change: function (event, ui) {
                var linkHref = options.url.replace('layered_navigation_slider_from', ui.values[0]).replace('layered_navigation_slider_to', ui.values[1]);

                if (!options.triggerApplyButton && !View.applyMode(options)) {
                    View.sendRequest(options, {
                        url: linkHref,
                        type: 'slider'
                    });
                }
            }
        });
    }

    renderLabel (value) {
        return this.options.template.replace('{amount}', value);
    }

    updateSliderData (from, to) {
        var slider = $('#' + this.options.sliderElement[0].id + '_slider');
        if (from > this.options.min || to < this.options.max) {
            slider.attr('data-value', from + '-' + to);
            slider.addClass('swatch-option-link-layered selected');
        } else {
            slider.attr('data-value', '');
            slider.removeClass('swatch-option-link-layered selected');
        }
    }
}
