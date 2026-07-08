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
            var ACTIONS_FOLDER = 'Digidirect_LayeredNavigation/js/dist/views/actions/';

            this.loadActions(ACTIONS_FOLDER);

            this.watchers();

            this.checkFilterRemember();
        }
        /**
         * Ajax update or redirect
         * @param options
         * @param data
         */


        _createClass(View, [{
            key: 'watchers',
            value: function watchers() {
                var _this = this;

                _store.Store.on(_store.Events.DATA_FETCH_PROGRESS, function () {
                    return _this.progress();
                });
                _store.Store.on(_store.Events.DATA_FETCH_SUCCESS, function (data) {
                    return _this.success(data);
                });
                _store.Store.on(_store.Events.ERROR, function (data) {
                    return _this.error(data);
                });
            }
        }, {
            key: '_renderData',
            value: function _renderData(data) {
                _jquery2.default.each(data, function (index, block) {
                    (0, _jquery2.default)(block.selector).each(function (index, selector) {
                        (0, _jquery2.default)(selector).html(block.block);
                        (0, _jquery2.default)(selector).trigger('contentUpdated');
                    });
                });
            }
        }, {
            key: 'loadActions',
            value: function loadActions(fileLocation) {
                var _this2 = this;

                if (this.options.triggerApplyButton || this.options.triggerApplyMode) {
                    require(['./' + fileLocation + 'apply'], function (Action) {
                        new Action(_this2.options);
                    });
                }

                if (this.options.enableAjax) {
                    require(['./' + fileLocation + 'toolbar'], function (Toolbar) {
                        new Toolbar(_this2.options);
                    });
                }
            }
        }, {
            key: 'isFilterRememberEnabled',
            value: function isFilterRememberEnabled() {
                return this.options.enableFilterRemember && !(0, _jquery2.default)(this.options.selectors.blockFilters).not(this.options.filterRememberDataAttr).length;
            }
        }, {
            key: 'checkFilterRemember',
            value: function checkFilterRemember() {
                if (this.isFilterRememberEnabled()) {
                    _store.Store.emit(_store.Events.DATA_FETCH_START, {
                        url: window.location.href,
                        isFilterRemember: true
                    });
                }
            }
        }, {
            key: 'showApplyAction',
            value: function showApplyAction() {
                var $applyAction = (0, _jquery2.default)(this.options.selectors.applyButton);

                if (this.options.enableFilterRemember && $applyAction.hasClass('-hide')) {
                    $applyAction.removeClass('-hide');
                }
            }
        }, {
            key: 'progress',
            value: function progress() {}
        }, {
            key: 'success',
            value: function success(data) {
                this._renderData(data);
                this.showApplyAction();
            }
        }, {
            key: 'error',
            value: function error() {}
        }], [{
            key: 'sendRequest',
            value: function sendRequest(options, data) {
                if (options.enableAjax) {
                    var AJAX_PARAM = 'ajax_navigation=true';
                    if (data.url.indexOf(AJAX_PARAM) === -1) {
                        if (data.url.indexOf('?') !== -1) {
                            data.url += '&' + AJAX_PARAM;
                        } else {
                            data.url += '?' + AJAX_PARAM;
                        }
                    }
                    _store.Store.emit(_store.Events.DATA_FETCH_START, data, options);
                } else {
                    window.location.href = data.url.replace(/\?$/, '');
                }
            }
        }, {
            key: 'applyMode',
            value: function applyMode(options) {
                if (options.triggerApplyMode && options.applyModeBreakpoint && window.matchMedia(options.applyModeBreakpoint).matches) {
                    return true;
                }
                return false;
            }
        }]);

        return View;
    }();

    exports.default = View;
    module.exports = exports['default'];
});
