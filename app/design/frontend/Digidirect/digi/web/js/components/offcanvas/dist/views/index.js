define(['module', 'exports', 'jquery', 'domReady!'], function (module, exports, _jquery) {
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

    function _objectWithoutProperties(obj, keys) {
        var target = {};

        for (var i in obj) {
            if (keys.indexOf(i) >= 0) continue;
            if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;
            target[i] = obj[i];
        }

        return target;
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
        function View(_ref) {
            var options = _ref.options,
                _store = _objectWithoutProperties(_ref, ['options']);

            _classCallCheck(this, View);

            this.Store = _store.store;
            this.Events = _store.events;
            this.element = _store.element;
            this.options = options;
            this.bind();
        }

        _createClass(View, [{
            key: 'bind',
            value: function bind() {
                this._checkElementsLength();
                this._initOffCanvas();
                this.actionsCall();
                this.watchers();
            }
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this = this;

                this.Store.on(this.Events.OFFCANVAS_INITIALIZED, function (data) {
                    return _this.initialized(data);
                });
                this.Store.on(this.Events.OFFCANVAS_OPEN, function (data) {
                    return _this.open(data);
                });
                this.Store.on(this.Events.OFFCANVAS_OPENED, function (data) {
                    return _this.opened(data);
                });
                this.Store.on(this.Events.OFFCANVAS_CLOSE, function (data) {
                    return _this.close(data);
                });
                this.Store.on(this.Events.OFFCANVAS_CLOSED, function (data) {
                    return _this.closed();
                });
                this.Store.on(this.Events.OFFCANVAS_TOOGLE, function (data, state) {
                    return _this.toggle(data, state);
                });
            }
        }, {
            key: '_checkElementsLength',
            value: function _checkElementsLength() {
                this.selectors = {
                    offCanvasWrapper: document.querySelector(this.options.offCanvasWrapperSelector),
                    offCanvasPanel: (0, _jquery2.default)(this.element.data('container'))
                };

                if (this.selectors.offCanvasWrapper.length && this.selectors.offCanvasPanel.length) {
                    throw new Error('Offcanvas Panel or Wrapper not found');
                }

                this._checkDirectionClasses();
            }
        }, {
            key: '_checkDirectionClasses',
            value: function _checkDirectionClasses() {
                this.options.rightPanelPresent = this.selectors.offCanvasPanel.hasClass(this.options.styleClasses.rightDirectionClass);
                this.options.leftPanelPresent = this.selectors.offCanvasPanel.hasClass(this.options.styleClasses.leftDirectionClass);

                if (!(this.options.rightPanelPresent || this.options.leftPanelPresent)) {
                    throw new Error('None offcanvas direction classes found for offCanvas panels elements');
                }
            }
        }, {
            key: '_initOffCanvas',
            value: function _initOffCanvas() {
                this.selectors.offCanvasWrapper.classList.add(this.options.styleClasses.initialisedClass);
                if (this.options.moveBodyOnOpen) {
                    this.selectors.offCanvasWrapper.classList.add('-offcanvas-overflow', this.options.styleClasses.wrapperClass);
                }

                this.Store.emit(this.Events.OFFCANVAS_INITIALIZED);
            }
        }, {
            key: 'actionsCall',
            value: function actionsCall() {
                var _this2 = this;

                // call offCanvas event on trigger element click
                this.element.on('click', function (e) {
                    _this2.Store.emit(_this2.Events.OFFCANVAS_TOOGLE, (0, _jquery2.default)(e.currentTarget), _this2.Store.currentState);
                });

                if (this.options.trigger) {
                    (0, _jquery2.default)(this.options.trigger).on('click', function (e) {
                        e.preventDefault();
                        _this2.Store.emit(_this2.Events.OFFCANVAS_TOOGLE, (0, _jquery2.default)(e.currentTarget), _this2.Store.currentState);
                    });
                }

                // close offCanvas on Esc press if config is true
                if (this.options.closeOnEsc) {
                    (0, _jquery2.default)(document).on('keyup', function (e) {
                        if (e.keyCode === _this2.Events.KEYCODE_ESC && _this2.Store.currentState === _this2.Events.OFFCANVAS_OPENED) {
                            _this2.Store.emit(_this2.Events.OFFCANVAS_CLOSE, _this2.element);
                        }
                    });
                }
            }
        }, {
            key: 'toggle',
            value: function toggle(element, currentState) {
                if (currentState === this.Events.OFFCANVAS_OPENED) {
                    // check if opened offcanvas should be closed and another one should NOT be opened
                    if (element.hasClass(this.options.styleClasses.activeClass)) {
                        this.Store.emit(this.Events.OFFCANVAS_CLOSE, element);
                        return false;
                    }
                    this.Store.emit(this.Events.OFFCANVAS_CLOSE, element);
                    return false;
                }
                this.Store.emit(this.Events.OFFCANVAS_OPEN, element);
            }
        }, {
            key: 'open',
            value: function open(element) {
                // add active class for trigger to show overlay
                element.addClass(this.options.styleClasses.activeClass);
                // add active class for panel and set aria-hidden
                (0, _jquery2.default)(element.data('container')).addClass(this.options.styleClasses.activeClass).attr('aria-hidden', 'false');
                // notify body that offcanvas opened
                document.querySelector('body').classList.add(this.options.styleClasses.offcanvasOpenedClass);
                // add defining class to offcanvas element if move body effect should be applied
                if (this.options.moveBodyOnOpen) {
                    this.selectors.offCanvasWrapper.classList.add(element.data('direction'));
                }
                this.Store.emit(this.Events.OFFCANVAS_OPENED);
            }
        }, {
            key: 'close',
            value: function close(element) {
                this.element.removeClass(this.options.styleClasses.activeClass);
                (0, _jquery2.default)(this.element.data('container')).removeClass(this.options.styleClasses.activeClass).attr('aria-hidden', 'true');
                document.querySelector('body').classList.remove(this.options.styleClasses.offcanvasOpenedClass);
                if (this.options.moveBodyOnOpen) {
                    this.selectors.offCanvasWrapper.classList.remove(this.options.styleClasses.rightDirectionClass, this.options.styleClasses.leftDirectionClass);
                }
                this.Store.emit(this.Events.OFFCANVAS_CLOSED);
            }
        }, {
            key: 'opened',
            value: function opened(data) {}
        }, {
            key: 'closed',
            value: function closed(data) {}
        }, {
            key: 'initialized',
            value: function initialized(data) {}
        }]);

        return View;
    }();

    exports.default = View;
    module.exports = exports['default'];
});
