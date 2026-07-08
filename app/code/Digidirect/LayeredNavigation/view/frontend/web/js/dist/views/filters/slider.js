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
                    $display = (0, _jquery2.default)('#' + elementID + '_display'),
                    $slider = (0, _jquery2.default)('#' + elementID + '_slider');

                self.updateSliderData(options.from, options.to);
                $display.html(self.renderLabel(options.from) + ' - ' + self.renderLabel(options.to));

                $slider.slider({
                    step: options.step,
                    range: true,
                    min: options.min,
                    max: options.max,
                    values: [options.from, options.to],
                    slide: function slide(event, ui) {
                        $display.html(self.renderLabel(ui.values[0]) + ' - ' + self.renderLabel(ui.values[1]));
                        self.updateSliderData(ui.values[0], ui.values[1]);
                    },
                    change: function change(event, ui) {
                        var linkHref = options.url.replace('layered_navigation_slider_from', ui.values[0]).replace('layered_navigation_slider_to', ui.values[1]);

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
