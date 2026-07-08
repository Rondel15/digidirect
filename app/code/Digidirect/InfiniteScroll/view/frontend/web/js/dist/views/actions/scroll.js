define(['module', 'exports', 'jquery', './../../common/store', './item-click'], function (module, exports, _jquery, _store, _itemClick) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _itemClick2 = _interopRequireDefault(_itemClick);

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

    var Action = function () {
        function Action(options, view) {
            _classCallCheck(this, Action);

            this.options = Object.assign({}, this.options, options);
            this._initScroll(this.options, view);
        }

        /**
         * Initialize scroll
         * @param options
         * @param view
         * @private
         */


        _createClass(Action, [{
            key: '_initScroll',
            value: function _initScroll(options, view) {
                var self = this;

                if (this.options.scrollToLastViewedItem) {
                    new _itemClick2.default(options);
                }

                (0, _jquery2.default)(view.options.scrollContainer).addClass('-scroll').on('scroll', function () {
                    if (self._loadOnScroll()) {
                        _store.Store.emit(_store.Events.DATA_FETCH_START, view.options.nextUrl);
                    }
                });

                if (this._preFill()) {
                    _store.Store.emit(_store.Events.DATA_FETCH_START, view.options.nextUrl);
                }
            }
        }, {
            key: '_getPosition',
            value: function _getPosition() {
                return document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
            }
        }, {
            key: '_getScrollThreshold',
            value: function _getScrollThreshold() {
                var $lastElement,
                    scrollOffset = this.options.scrollOffset;

                scrollOffset = scrollOffset >= 0 ? scrollOffset * -1 : scrollOffset;

                $lastElement = (0, _jquery2.default)(this.options.itemsContainerSelector).find(this.options.itemSelector).last();

                // if the don't have a last element, the DOM might not have been loaded,
                // or the selector is invalid
                if ($lastElement.length === 0) {
                    return;
                }

                return $lastElement.offset().top + $lastElement.height() + scrollOffset;
            }
        }, {
            key: '_getCurrentScrollOffset',
            value: function _getCurrentScrollOffset() {
                var scrollTop = 0,
                    $container = (0, _jquery2.default)(this.options.scrollContainer),
                    containerHeight = $container.height();

                if (this._isWindowContainer($container)) {
                    scrollTop = this._getPosition();
                    containerHeight = document.documentElement.clientHeight;
                } else {
                    scrollTop = $container.offset().top;
                }

                return scrollTop + containerHeight;
            }
        }, {
            key: '_hasScrollBar',
            value: function _hasScrollBar() {
                var $container = (0, _jquery2.default)(this.options.scrollContainer);

                if (this._isWindowContainer($container)) {
                    $container = (0, _jquery2.default)('body');
                }
                return $container.get(0).scrollHeight > $container.height();
            }
        }, {
            key: '_loadOnScroll',
            value: function _loadOnScroll() {
                var currentScrollOffset = this._getCurrentScrollOffset(),
                    scrollThreshold = this._getScrollThreshold();

                return currentScrollOffset >= scrollThreshold;
            }
        }, {
            key: '_isWindowContainer',
            value: function _isWindowContainer(container) {
                return window === container.get(0);
            }
        }, {
            key: '_preFill',
            value: function _preFill() {
                return this.options.preFill && !this._hasScrollBar();
            }
        }]);

        return Action;
    }();

    exports.default = Action;
    module.exports = exports['default'];
});
