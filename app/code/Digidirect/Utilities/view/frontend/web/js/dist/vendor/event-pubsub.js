define(['module', 'exports'], function (module, exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

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

    var EventPubSub = function () {
        function EventPubSub(scope) {
            _classCallCheck(this, EventPubSub);

            this._events_ = {};
            this.publish = this.trigger = this.emit;
            this.subscribe = this.on;
            this.unSubscribe = this.off;
        }

        _createClass(EventPubSub, [{
            key: 'on',
            value: function on(type, handler) {
                if (!handler) {
                    var err = new ReferenceError('handler not defined.');
                    throw err;
                }

                if (!this._events_[type]) {
                    this._events_[type] = [];
                }

                this._events_[type].push(handler);
                return this;
            }
        }, {
            key: 'off',
            value: function off(type, handler) {
                if (!this._events_[type]) {
                    return this;
                }

                if (!handler) {
                    var err = new ReferenceError('handler not defined. if you wish to remove all handlers from the event please pass "*" as the handler');
                    throw err;
                }

                if (handler == '*') {
                    delete this._events_[type];
                    return this;
                }

                var handlers = this._events_[type];

                while (handlers.includes(handler)) {
                    handlers.splice(handlers.indexOf(handler), 1);
                }

                if (handlers.length < 1) {
                    delete this._events_[type];
                }

                return this;
            }
        }, {
            key: 'emit',
            value: function emit(type) {
                for (var _len = arguments.length, args = Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
                    args[_key - 1] = arguments[_key];
                }

                if (!this._events_[type]) {
                    return this.emit$.apply(this, [type].concat(args));
                }

                var handlers = this._events_[type];

                var _iteratorNormalCompletion = true;
                var _didIteratorError = false;
                var _iteratorError = undefined;

                try {
                    for (var _iterator = handlers[Symbol.iterator](), _step; !(_iteratorNormalCompletion = (_step = _iterator.next()).done); _iteratorNormalCompletion = true) {
                        var handler = _step.value;

                        handler.apply(this, args);
                    }
                } catch (err) {
                    _didIteratorError = true;
                    _iteratorError = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion && _iterator.return) {
                            _iterator.return();
                        }
                    } finally {
                        if (_didIteratorError) {
                            throw _iteratorError;
                        }
                    }
                }

                return this.emit$.apply(this, [type].concat(args));
            }
        }, {
            key: 'emit$',
            value: function emit$(type) {
                if (!this._events_['*']) {
                    return this;
                }

                var catchAll = this._events_['*'];

                for (var _len2 = arguments.length, args = Array(_len2 > 1 ? _len2 - 1 : 0), _key2 = 1; _key2 < _len2; _key2++) {
                    args[_key2 - 1] = arguments[_key2];
                }

                var _iteratorNormalCompletion2 = true;
                var _didIteratorError2 = false;
                var _iteratorError2 = undefined;

                try {
                    for (var _iterator2 = catchAll[Symbol.iterator](), _step2; !(_iteratorNormalCompletion2 = (_step2 = _iterator2.next()).done); _iteratorNormalCompletion2 = true) {
                        var handler = _step2.value;

                        handler.call.apply(handler, [this, type].concat(args));
                    }
                } catch (err) {
                    _didIteratorError2 = true;
                    _iteratorError2 = err;
                } finally {
                    try {
                        if (!_iteratorNormalCompletion2 && _iterator2.return) {
                            _iterator2.return();
                        }
                    } finally {
                        if (_didIteratorError2) {
                            throw _iteratorError2;
                        }
                    }
                }

                return this;
            }
        }]);

        return EventPubSub;
    }();

    exports.default = EventPubSub;
    module.exports = exports['default'];
});
