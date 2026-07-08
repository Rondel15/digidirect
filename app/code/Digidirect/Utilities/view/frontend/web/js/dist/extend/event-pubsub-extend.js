define(['module', 'exports', './../vendor/event-pubsub', './emit-extend'], function (module, exports, _eventPubsub, _emitExtend) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _eventPubsub2 = _interopRequireDefault(_eventPubsub);

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

    var EventPubSubExtended = function (_EventPubSub) {
        _inherits(EventPubSubExtended, _EventPubSub);

        function EventPubSubExtended(scope) {
            _classCallCheck(this, EventPubSubExtended);

            var _this = _possibleConstructorReturn(this, (EventPubSubExtended.__proto__ || Object.getPrototypeOf(EventPubSubExtended)).call(this, scope));

            _this.scope = scope;
            return _this;
        }

        _createClass(EventPubSubExtended, [{
            key: 'emit',
            value: function emit(type) {
                var _get2;

                (0, _emitExtend.emitExtend)(this, type, this.scope);

                for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
                    args[_key - 1] = arguments[_key];
                }

                (_get2 = _get(EventPubSubExtended.prototype.__proto__ || Object.getPrototypeOf(EventPubSubExtended.prototype), 'emit', this)).call.apply(_get2, [this, type].concat(args));
            }
        }]);

        return EventPubSubExtended;
    }(_eventPubsub2.default);

    exports.default = EventPubSubExtended;
    module.exports = exports['default'];
});
