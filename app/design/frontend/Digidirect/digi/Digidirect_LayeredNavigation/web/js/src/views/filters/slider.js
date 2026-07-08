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
            $wrapper = $('#' + elementID),
            $fromInput = $wrapper.find('.slider-value.-from'),
            $toInput = $wrapper.find('.slider-value.-to'),
            $slider = $('#' + elementID + '_slider');

        self.updateSliderData(options.from, options.to);
        self._bindInputs($wrapper, $slider, options);

        $slider.slider({
            step: options.step,
            range: true,
            min: options.min,
            max: options.max,
            values: [options.from, options.to],
            slide: function (event, ui) {
                $fromInput.val(ui.values[0]);
                $toInput.val(ui.values[1]);
            },
            change: function (event, ui) {
                var linkHref = options.url.replace('layered_navigation_slider_from', ui.values[0]).replace('layered_navigation_slider_to', ui.values[1]);

                self.updateSliderData(ui.values[0], ui.values[1]);

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

    _bindInputs (wrapper, slider) {
        var $this = this,
            inputs = wrapper.find('.slider-value');

        inputs.on('change', function () {
            var $self = $(this),
                $fromValue = parseInt(inputs.filter('.-from').val()),
                $toValue = parseInt(inputs.filter('.-to').val());

            if (isNaN($fromValue)) {
                $fromValue = $this.options.min;
                $self.val($fromValue);
                slider.slider('values', 0, $fromValue);
            }

            if (isNaN($toValue)) {
                $toValue = $this.options.max;
                $self.val($toValue);
                slider.slider('values', 1, $toValue);
            }            

            if ($self.is('.-from')) {
                $fromValue = parseInt($self.val());

                if ($fromValue >= $toValue) {
                    $fromValue = $toValue - $this.options.step;
                } else if ($fromValue < $this.options.min) {
                    $fromValue = $this.options.min;
                }

                $self.val($fromValue);
                slider.slider('values', 0, $fromValue);

            }

            if ($self.is('.-to')) {
                $toValue = parseInt($self.val());

                if ($toValue <= $fromValue) {
                    $toValue = $fromValue + $this.options.step;

                } else if ($toValue > $this.options.max) {
                    $toValue = $this.options.max;
                }

                $self.val($toValue);
                slider.slider('values', 1, $toValue);
            }
        });
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
