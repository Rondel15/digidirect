define(['module', 'exports', 'jquery', './../index'], function (module, exports, _jquery, _index) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _index2 = _interopRequireDefault(_index);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var _createClass = function () {
        function defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }

        return function (Constructor, protoProps, staticProps) {
            if (protoProps) defineProperties(Constructor.prototype, protoProps);
            if (staticProps) defineProperties(Constructor, staticProps);
            return Constructor;
        };
    }();

    var Link = function () {
        function Link(options) {
            _classCallCheck(this, Link);

            this.options = options;
            this.watchers(this.options);
        }

        _createClass(Link, [{
            key: 'watchers',
            value: function watchers(options) {
                var self = this,
                    elementID = options.sliderElement[0].id,
                    $wrapper = (0, _jquery2.default)('#' + elementID),
                    $fromInput = $wrapper.find('.slider-value.-from'),
                    $toInput = $wrapper.find('.slider-value.-to'),
                    $slider = (0, _jquery2.default)('#' + elementID + '_slider');

                self.updateSliderData(options.from, options.to);
                self._bindInputs($wrapper, $slider, options);

                $slider.slider({
                    step: options.step,
                    range: true,
                    min: options.min,
                    max: options.max,
                    values: [options.from, options.to],
                    slide: function slide(event, ui) {
                        $fromInput.val(ui.values[0]);
                        $toInput.val(ui.values[1]);
                    },
                    change: function change(event, ui) {
                        var linkHref = options.url.replace('layered_navigation_slider_from', ui.values[0]).replace('layered_navigation_slider_to', ui.values[1]);

                        self.updateSliderData(ui.values[0], ui.values[1]);

                        if (!options.triggerApplyButton && !_index2.default.applyMode(options)) {
                            _index2.default.sendRequest(options, {
                                url: linkHref,
                                type: 'slider'
                            });
                        }
                    }
                });
            }
        }, {
            key: 'renderLabel',
            value: function renderLabel(value) {
                return this.options.template.replace('{amount}', value);
            }
        }, {
            key: '_bindInputs',
            value: function _bindInputs(wrapper, slider) {
                var $this = this,
                    inputs = wrapper.find('.slider-value');

                inputs.on('change', function () {
                    var $self = (0, _jquery2.default)(this),
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
        }, {
            key: 'updateSliderData',
            value: function updateSliderData(from, to) {
                var slider = (0, _jquery2.default)('#' + this.options.sliderElement[0].id + '_slider');

                if (from > this.options.min || to < this.options.max) {
                    slider.attr('data-value', from + '-' + to);
                    slider.addClass('swatch-option-link-layered selected');
                } else {
                    slider.attr('data-value', '');
                    slider.removeClass('swatch-option-link-layered selected');
                }
            }
        }]);

        return Link;
    }();

    exports.default = Link;
    module.exports = exports['default'];
});
