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
                var $link = (0, _jquery2.default)(options.linkElement[0]),
                    url = $link.attr('href');

                $link.on('click', function (e) {
                    e.preventDefault();

                    if (options.resetParameters || !options.triggerApplyButton && !_index2.default.applyMode(options)) {
                        _index2.default.sendRequest(options, {
                            url: url,
                            type: 'link'
                        });
                    }
                });
            }
        }]);

        return Link;
    }();

    exports.default = Link;
    module.exports = exports['default'];
});
