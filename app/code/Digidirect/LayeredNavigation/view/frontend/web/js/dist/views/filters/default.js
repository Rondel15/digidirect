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

    var Default = function () {
        function Default(options) {
            _classCallCheck(this, Default);

            this.options = options;

            this.watchers(this.options);
        }

        _createClass(Default, [{
            key: 'watchers',
            value: function watchers(options) {
                var element = options.element,
                    checkbox = element.find('input'),
                    multipleSelect = element.data('multiselect'),
                    url;

                element.on('click', function (e) {
                    if (e.target.nodeName.toLowerCase() !== 'input') {
                        e.preventDefault();
                        checkbox.prop('checked', !checkbox.prop('checked'));
                    }

                    if (element.find('.swatch-option.disabled').length) {
                        return false;
                    }

                    if (!multipleSelect) {
                        element.closest(options.selectors.filterContent).find('a').each(function () {
                            var $this = (0, _jquery2.default)(this);
                            if ($this.attr('href') !== element.attr('href')) {
                                $this.removeClass('selected');
                                $this.find('.swatch-option').removeClass('selected');
                                $this.find('input').prop('checked', false);
                            }
                        });
                    }

                    if (element.hasClass('selected')) {
                        element.removeClass('selected');
                        element.find('.swatch-option').removeClass('selected');
                    } else {
                        element.addClass('selected');
                        element.find('.swatch-option').addClass('selected');
                    }

                    if (!options.triggerApplyButton && !_index2.default.applyMode(options)) {
                        url = element.attr('href');
                        element.find('.swatch-option').trigger('mouseleave');
                        _index2.default.sendRequest(options, {
                            url: url,
                            type: 'default'
                        });
                    }
                });
            }
        }]);

        return Default;
    }();

    exports.default = Default;
    module.exports = exports['default'];
});
