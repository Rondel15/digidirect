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

    function _possibleConstructorReturn(self, call) {
        if (!self) {
            throw new ReferenceError("this hasn't been initialised - super() hasn't been called");
        }

        return call && (typeof call === "object" || typeof call === "function") ? call : self;
    }

    var _get = function get(object, property, receiver) {
        if (object === null) object = Function.prototype;
        var desc = Object.getOwnPropertyDescriptor(object, property);

        if (desc === undefined) {
            var parent = Object.getPrototypeOf(object);

            if (parent === null) {
                return undefined;
            } else {
                return get(parent, property, receiver);
            }
        } else if ("value" in desc) {
            return desc.value;
        } else {
            var getter = desc.get;

            if (getter === undefined) {
                return undefined;
            }

            return getter.call(receiver);
        }
    };

    function _inherits(subClass, superClass) {
        if (typeof superClass !== "function" && superClass !== null) {
            throw new TypeError("Super expression must either be null or a function, not " + typeof superClass);
        }

        subClass.prototype = Object.create(superClass && superClass.prototype, {
            constructor: {
                value: subClass,
                enumerable: false,
                writable: true,
                configurable: true
            }
        });
        if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass;
    }

    var ViewCustom = function (_View) {
        _inherits(ViewCustom, _View);

        function ViewCustom(options) {
            _classCallCheck(this, ViewCustom);

            var _this = _possibleConstructorReturn(this, (ViewCustom.__proto__ || Object.getPrototypeOf(ViewCustom)).call(this, options));

            _this.reviewsWrapper = (0, _jquery2.default)('#customer-reviews .block-content');
            return _this;
        }

        _createClass(ViewCustom, [{
            key: '_renderData',
            value: function _renderData(data) {
                var $data = (0, _jquery2.default)(data.content);
                (0, _jquery2.default)(this.options.itemsContainerSelector).find('.review-items').append($data.find('.review-items').html());
            }
        }, {
            key: 'progress',
            value: function progress() {
                var self = this;
                this.reviewsWrapper.loader({ 'icon': self.options.loaderIcon, 'texts': { 'loaderText': self.options.loaderText } }).trigger('processStart');
            }
        }, {
            key: 'success',
            value: function success(data) {
                _get(ViewCustom.prototype.__proto__ || Object.getPrototypeOf(ViewCustom.prototype), 'success', this).call(this, data);
                this.reviewsWrapper.loader().trigger('processStop');
            }
        }, {
            key: 'finish',
            value: function finish() {
                this.reviewsWrapper.find('.infinitescroll-button').remove();
            }
        }]);

        return ViewCustom;
    }(_index2.default);

    exports.default = ViewCustom;
    module.exports = exports['default'];
});
