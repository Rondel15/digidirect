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

    var Dropdown = function () {
        function Dropdown(options) {
            _classCallCheck(this, Dropdown);

            this.options = options;
            this.watchers(this.options);
        }

        _createClass(Dropdown, [{
            key: 'watchers',
            value: function watchers(options) {
                var $select = (0, _jquery2.default)(options.dropdownElement[0]),
                    option;
                $select.on('change', function () {
                    if (options.triggerApplyButton || _index2.default.applyMode(options)) {
                        option = $select.find('option:selected');
                        $select.find('option').removeClass('selected');
                        option.addClass('swatch-option-link-layered selected');
                        option.attr('href', option.attr('value'));
                    } else {
                        _index2.default.sendRequest(options, {
                            url: $select.val(),
                            type: 'dropdown'
                        });
                    }
                });
            }
        }]);

        return Dropdown;
    }();

    exports.default = Dropdown;
    module.exports = exports['default'];
});
