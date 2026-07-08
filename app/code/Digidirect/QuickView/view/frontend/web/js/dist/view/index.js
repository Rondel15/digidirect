define(['module', 'exports', 'jquery', 'Magento_Customer/js/customer-data', './../common/store', 'loader', 'Magento_Ui/js/modal/modal'], function (module, exports, _jquery, _customerData, _store) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _customerData2 = _interopRequireDefault(_customerData);

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
            this.init();
            this.watchers(this.options);
            this.bind();
        }

        /**
         *  Init loader, init popup
         */


        _createClass(View, [{
            key: 'init',
            value: function init() {
                Object.assign(this.options.modal, { closed: this.closed });
                this.loader = (0, _jquery2.default)(this.options.loaderContainer).loader({ 'icon': this.options.loaderIcon, 'texts': { 'loaderText': this.options.loaderText } });
                this.popup = (0, _jquery2.default)(this.options.contentContainer).modal(this.options.modal);
                this.iframe = (0, _jquery2.default)(this.options.iframe);
                this.iframeIsLoaded = false;
            }
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this = this;

                _store.Store.on(_store.Events.DATA_FETCH_PROGRESS, function () {
                    return _this.progress();
                });
                _store.Store.on(_store.Events.DATA_FETCH_SUCCESS, function (data) {
                    return _this.success(data);
                });
                _store.Store.on(_store.Events.DATA_FETCH_FINISH, function () {
                    return _this.finish();
                });
                _store.Store.on(_store.Events.QUICK_VIEW_OPEN, function () {
                    return _this.open();
                });
                _store.Store.on(_store.Events.QUICK_VIEW_OPENED, function () {
                    return _this.opened();
                });
                _store.Store.on(_store.Events.QUICK_VIEW_CLOSE, function () {
                    return _this.close();
                });
                _store.Store.on(_store.Events.QUICK_VIEW_CLOSED, function () {
                    return _this.closed();
                });
                _store.Store.on(_store.Events.SECTION_UPDATE, function (section) {
                    return _this.updateSectionData(section);
                });
                _store.Store.on(_store.Events.ADD_TO_CART, function (context, options, form) {
                    return _this.addToCart(context, options, form);
                });
            }
        }, {
            key: 'bind',
            value: function bind() {
                var _this2 = this;

                var self = this;
                (0, _jquery2.default)(document).on('click', this.options.button, function () {
                    var url = (0, _jquery2.default)(this).data('url');
                    if (url !== self.activeUrl) {
                        _store.Store.emit(_store.Events.DATA_FETCH_START, url);
                    } else {
                        self.open();
                    }
                    self.activeUrl = url;
                });

                //  Mobile listeners
                if (this.options.isMobileEnabled) {
                    this.mobileBind();
                }

                //  Transfer the Store and Events to the iframe, even if the iframe rebooted
                this.iframe.on('load', function () {
                    _this2.delegateStore();
                    if (_this2.isTouchDevice() && _this2.iframeIsLoaded) {
                        _this2.bindTouchObserver();
                    }
                    _this2.iframeIsLoaded = true;
                });
            }
        }, {
            key: 'mobileBind',
            value: function mobileBind() {
                var self = this;
                (0, _jquery2.default)(document).on('click', this.options.mobileListener, function (e) {
                    var button = (0, _jquery2.default)(this).closest(self.options.itemSelector).find(self.options.button);
                    if (button.is(':hidden')) {
                        button.trigger('click');
                        e.preventDefault();
                    }
                });
            }
        }, {
            key: 'bindTouchObserver',
            value: function bindTouchObserver() {
                var iframeObserveElement = this.iframe.contents().find('#maincontent')[0],
                    iframeWindow = (0, _jquery2.default)(this.iframe[0].contentWindow),
                    observer = new MutationObserver(function () {
                    iframeWindow.trigger('resize');
                });

                observer.observe(iframeObserveElement, {
                    attributes: true,
                    subtree: true,
                    attributeFilter: ['style']
                });

                iframeWindow.on('resize', _jquery2.default.proxy(this.setIframeHeight, this));
            }
        }, {
            key: 'setIframeHeight',
            value: function setIframeHeight() {
                (0, _jquery2.default)(this.iframe).height(this.iframe.contents().find('body').height());
            }
        }, {
            key: 'progress',
            value: function progress() {
                this.loader.trigger('processStart');
                this.clearIframe();
            }
        }, {
            key: 'success',
            value: function success(data) {
                this._renderData(data);
                this.open();
                this._historyIframe();
            }
        }, {
            key: 'finish',
            value: function finish() {
                this.loader.trigger('processStop');
            }
        }, {
            key: 'delegateStore',
            value: function delegateStore() {
                this.iframe[0].contentWindow.globalEvents = _store.Events;
                this.iframe[0].contentWindow.globalStore = _store.Store;
            }
        }, {
            key: '_renderData',
            value: function _renderData(data) {
                var iframe = this.iframe[0].contentDocument.open();
                iframe.write(data);
                iframe.close();
            }
        }, {
            key: '_historyIframe',
            value: function _historyIframe() {
                this.iframe[0].contentWindow.history.pushState('', '', this.activeUrl);
            }
        }, {
            key: 'updateSectionData',
            value: function updateSectionData(section) {
                _customerData2.default.reload(section, true);
            }
        }, {
            key: 'clearIframe',
            value: function clearIframe() {
                //  Clear iframe for correct work document.write()
                this.iframe.attr('src', 'about:blank');
                this.iframeIsLoaded = false;
            }
        }, {
            key: 'addToCart',
            value: function addToCart(context, options, form) {
                var self = context;
                (0, _jquery2.default)(options.minicartSelector).trigger('contentLoading');
                self.disableAddToCartButton(form);

                _jquery2.default.ajax({
                    url: form.attr('action'),
                    data: form.serialize(),
                    type: 'post',
                    dataType: 'json',
                    beforeSend: function beforeSend() {
                        if (self.isLoaderEnabled()) {
                            (0, _jquery2.default)('body').trigger(options.processStart);
                        }
                    },
                    success: function success(res) {
                        if (self.isLoaderEnabled()) {
                            (0, _jquery2.default)('body').trigger(options.processStop);
                        }

                        if (res.backUrl) {
                            window.location = res.backUrl;
                            return;
                        }
                        if (res.messages) {
                            (0, _jquery2.default)(options.messagesSelector).html(res.messages);
                        }
                        if (res.minicart) {
                            (0, _jquery2.default)(options.minicartSelector).replaceWith(res.minicart);
                            (0, _jquery2.default)(options.minicartSelector).trigger('contentUpdated');
                        }
                        if (res.product && res.product.statusText) {
                            (0, _jquery2.default)(options.productStatusSelector).removeClass('available').addClass('unavailable').find('span').html(res.product.statusText);
                        }
                        self.enableAddToCartButton(form);
                    }
                });
            }
        }, {
            key: 'isTouchDevice',
            value: function isTouchDevice() {
                return 'ontouchstart' in document.documentElement;
            }
        }, {
            key: 'open',
            value: function open() {
                this.popup.modal('openModal');
            }
        }, {
            key: 'opened',
            value: function opened() {}
        }, {
            key: 'close',
            value: function close() {
                this.popup.modal('closeModal');
            }
        }, {
            key: 'closed',
            value: function closed() {
                if (_jquery2.default.localStorage.get('isChangedWishList')) {
                    _store.Store.emit(_store.Events.SECTION_UPDATE, 'wishlist');
                    _jquery2.default.localStorage.remove('isChangedWishList');
                }
            }
        }]);

        return View;
    }();

    exports.default = View;
    module.exports = exports['default'];
});
