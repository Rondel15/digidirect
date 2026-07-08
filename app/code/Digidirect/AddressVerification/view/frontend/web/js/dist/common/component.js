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
        function Component(options, mode) {
            _classCallCheck(this, Component);

            this.options = Object.assign({}, this.options, options);
            this.model = new _index2.default(this.options);

            this.bind(this.options, mode);
            this.watchers();
        }

        _createClass(Component, [{
            key: 'bind',
            value: function bind(options, mode) {
                switch (mode) {
                    case 'post':
                        (0, _digidirectUtils.loadView)(options, this, 'Digidirect_AddressVerification/js/dist/views/post', 'Address Verification (Post)');
                        break;
                    default:
                        (0, _digidirectUtils.loadView)(options, this, 'Digidirect_AddressVerification/js/dist/views/index', 'Address Verification (Google API)');
                }
            }
        }, {
            key: 'fetchData',
            value: function () {
                var _ref = _asyncToGenerator( /*#__PURE__*/regeneratorRuntime.mark(function _callee(url, fetchOptions) {
                    var response = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : undefined;
                    var value = arguments[3];
                    var data;
                    return regeneratorRuntime.wrap(function _callee$(_context) {
                        while (1) {
                            switch (_context.prev = _context.next) {
                                case 0:
                                    _context.prev = 0;

                                    if (url) {
                                        _context.next = 3;
                                        break;
                                    }

                                    return _context.abrupt('return');

                                case 3:

                                    // start progress
                                    _store.Store.emit(_store.Events.POST_FETCH_DATA_PROGRESS);

                                    // model fetch data
                                    _context.next = 6;
                                    return this.model.fetchData(url, fetchOptions);

                                case 6:
                                    data = _context.sent;


                                    _store.Store.emit(_store.Events.POST_FETCH_DATA_SUCCESS, data, response, value);
                                    _context.next = 14;
                                    break;

                                case 10:
                                    _context.prev = 10;
                                    _context.t0 = _context['catch'](0);

                                    console.warn('Address Verification request failed', _context.t0);
                                    _store.Store.emit(_store.Events.POST_FETCH_DATA_ERROR, _context.t0);

                                case 14:
                                case 'end':
                                    return _context.stop();
                            }
                        }
                    }, _callee, this, [[0, 10]]);
                }));

                function fetchData(_x, _x2) {
                    return _ref.apply(this, arguments);
                }

                return fetchData;
            }()
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this = this;

                var self = this;
                _store.Store.on(_store.Events.POST_FETCH_DATA_START, function (url, data, response, value) {
                    url += self.getParams(data);
                    _this.fetchData(url, {
                        method: 'GET'
                    }, response, value);
                });
            }
        }, {
            key: 'getParams',
            value: function getParams(data) {
                var params = '';
                if (data === undefined || Object.keys(data).length === 0) {
                    return '';
                }
                Object.keys(data).forEach(function (key) {
                    if (params !== '') {
                        params += '&';
                    }
                    params += key + '=' + encodeURIComponent(data[key]);
                });
                return '?' + params;
            }
        }]);

        return Component;
    }();

    exports.default = Component;
    module.exports = exports['default'];
});
