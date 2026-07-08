define(['module', 'exports', 'jquery', './../common/store'], function (module, exports, _jquery, _store) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

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

    var View = function () {
        function View(options) {
            _classCallCheck(this, View);

            this.options = Object.assign({}, this.options, options);
            this.watchers();

            if (this.options.rememberScrollState) {
                this._restoreState();
            }

            this._loadAction(this.options);
        }

        _createClass(View, [{
            key: '_loadAction',
            value: function _loadAction(options) {
                var _this = this;

                var ACTIONS_FOLDER = 'Digidirect_InfiniteScroll/js/dist/views/actions/';
                if (options.nextUrl && options.action || this.options.rememberScrollState) {
                    require(['./' + ACTIONS_FOLDER + options.action], function (Action) {
                        _this.actionData = new Action(options, _this);
                    });
                }
            }
        }, {
            key: '_restoreState',
            value: function _restoreState() {
                var state = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey));

                if (state && window.location.href === state.location && window.localStorage.getItem(this.options.itemUrlKey)) {
                    (0, _jquery2.default)(this.options.itemsContainerSelector).append(state.content).trigger('contentUpdated');
                    this.options.currentCount = state.currentCount;
                    this.options.totalCount = state.totalCount;
                    this.options.nextUrl = state.nextUrl;
                } else {
                    window.localStorage.removeItem(this.options.scrollStateKey);
                }
            }
        }, {
            key: '_saveState',
            value: function _saveState(data) {
                var currentState = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey)),
                    newState = {
                    location: window.location.href,
                    nextUrl: data.url,
                    totalCount: data.totalCount,
                    currentCount: data.currentCount,
                    perPageCount: data.perPageCount
                },
                    content = '';

                if (currentState && window.location.href === currentState.location) {
                    content = currentState.content;
                }

                newState.content = content + data.content;

                window.localStorage.setItem(this.options.scrollStateKey, JSON.stringify(newState));
            }
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this2 = this;

                _store.Store.on(_store.Events.DATA_FETCH_PROGRESS, function () {
                    return _this2.progress();
                });
                _store.Store.on(_store.Events.DATA_FETCH_SUCCESS, function (data) {
                    return _this2.success(data);
                });
                _store.Store.on(_store.Events.DATA_FETCH_FINISH, function (data) {
                    return _this2.finish(data);
                });
                _store.Store.on(_store.Events.RELOAD, function (options) {
                    return _this2.reload(options);
                });
                _store.Store.on(_store.Events.ERROR, function (data) {
                    return _this2.error(data);
                });
            }
        }, {
            key: 'unwatch',
            value: function unwatch() {
                (0, _jquery2.default)(this.options.scrollContainer).off('scroll');

                _store.Store.off(_store.Events.DATA_FETCH_PROGRESS, '*');
                _store.Store.off(_store.Events.DATA_FETCH_SUCCESS, '*');
                _store.Store.off(_store.Events.DATA_FETCH_FINISH, '*');
                _store.Store.off(_store.Events.RELOAD, '*');
                _store.Store.off(_store.Events.ERROR, '*');
            }
        }, {
            key: '_renderData',
            value: function _renderData(data) {
                (0, _jquery2.default)(this.options.itemsContainerSelector).append(data.content);
            }
        }, {
            key: 'progress',
            value: function progress() {}
        }, {
            key: 'success',
            value: function success(data) {
                this._renderData(data);
                this.options.nextUrl = data.url;
                (0, _jquery2.default)(this.options.itemsContainerSelector).trigger('contentUpdated');

                if (this.options.rememberScrollState) {
                    this._saveState(data);
                }
            }
        }, {
            key: 'finish',
            value: function finish() {}
        }, {
            key: 'reload',
            value: function reload(options) {
                this.unwatch();

                new View(options);
            }
        }, {
            key: 'error',
            value: function error() {}
        }]);

        return View;
    }();

    exports.default = View;
    module.exports = exports['default'];
});
