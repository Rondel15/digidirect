define(['module', 'exports', './../models/index', './store', 'digidirectUtils'], function (module, exports, _index, _store, _digidirectUtils) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _index2 = _interopRequireDefault(_index);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _asyncToGenerator(fn) {
        return function () {
            var gen = fn.apply(this, arguments);
            return new Promise(function (resolve, reject) {
                function step(key, arg) {
                    try {
                        var info = gen[key](arg);
                        var value = info.value;
                    } catch (error) {
                        reject(error);
                        return;
                    }

                    if (info.done) {
                        resolve(value);
                    } else {
                        return Promise.resolve(value).then(function (value) {
                            step("next", value);
                        }, function (err) {
                            step("throw", err);
                        });
                    }
                }

                return step("next");
            });
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

    var Component = function () {
        function Component(options) {
            _classCallCheck(this, Component);

            this.options = options;
            this.model = new _index2.default(this.options);
            this.bind(this.options);
            this.watchers(this.options);
        }

        _createClass(Component, [{
            key: 'bind',
            value: function bind(options) {
                (0, _digidirectUtils.loadView)(options, this, 'Digidirect_QuickView/js/dist/view/index', 'Quick View');
            }
        }, {
            key: 'watchers',
            value: function watchers(options) {
                var _this = this;

                _store.Store.on(_store.Events.DATA_FETCH_START, function (url) {
                    if (_store.Store.currentState === _store.Events.DATA_FETCH_PROGRESS) {
                        return true;
                    }
                    _this.fetchData(url);
                });
            }
        }, {
            key: 'fetchData',
            value: function () {
                var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(url) {
                    var data;
                    return regeneratorRuntime.wrap(function _callee$(_context) {
                        while (1) {
                            switch (_context.prev = _context.next) {
                                case 0:
                                    _store.Store.emit(_store.Events.DATA_FETCH_PROGRESS);
                                    _context.prev = 1;

                                    if (url) {
                                        _context.next = 4;
                                        break;
                                    }

                                    return _context.abrupt('return', true);

                                case 4:
                                    _context.next = 6;
                                    return this.model.fetchData(url);

                                case 6:
                                    data = _context.sent;

                                    _store.Store.emit(_store.Events.DATA_FETCH_SUCCESS, data);
                                    _context.next = 14;
                                    break;

                                case 10:
                                    _context.prev = 10;
                                    _context.t0 = _context['catch'](1);

                                    console.warn('Quick View request failed', _context.t0);
                                    _store.Store.emit(_store.Events.ERROR, _context.t0);

                                case 14:
                                    _store.Store.emit(_store.Events.DATA_FETCH_FINISH);

                                case 15:
                                case 'end':
                                    return _context.stop();
                            }
                        }
                    }, _callee, this, [[1, 10]]);
                }));

                function fetchData(_x) {
                    return _ref.apply(this, arguments);
                }

                return fetchData;
            }()
        }]);

        return Component;
    }();

    exports.default = Component;
    module.exports = exports['default'];
});
