define(['module', 'exports', 'jquery', './../index', 'text!Digidirect_InfiniteScroll/template/catalog-listing/button.html', './../actions/update-amount', 'Magento_Customer/js/customer-data'], function (module, exports, _jquery, _index, _button, _updateAmount, _customerData) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _index2 = _interopRequireDefault(_index);

    var _button2 = _interopRequireDefault(_button);

    var _updateAmount2 = _interopRequireDefault(_updateAmount);

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

            options.buttonTemplate = _button2.default;

            var _this = _possibleConstructorReturn(this, (ViewCustom.__proto__ || Object.getPrototypeOf(ViewCustom)).call(this, options));

            _this.productsWrapper = (0, _jquery2.default)('.products.wrapper');

            if (_this.options.rememberScrollState) {
                _this._restoreCatalogState();
            }
            return _this;
        }

        /**
         * Restore previously saved scroll state
         * @private
         */


        _createClass(ViewCustom, [{
            key: '_restoreCatalogState',
            value: function _restoreCatalogState() {
                var state = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey));

                if (state && window.location.href === state.location && window.localStorage.getItem(this.options.itemUrlKey)) {
                    this.options.buttonContent = this._setButtonContent(state.currentCount, state.totalCount);
                    this._refreshInit(state);
                }
            }
        }, {
            key: '_refreshInit',
            value: function _refreshInit(data) {
                new _updateAmount2.default(data.perPageCount, data.currentCount, data.totalCount);
                this.productsWrapper.find('[data-role=tocart-form], .form.map.checkout').catalogAddToCart();

                if ((0, _jquery2.default)('#form-tmpl-multiple').length) {
                    var multiplewishlist = _customerData2.default.get('multiplewishlist'),
                        newItems = (0, _jquery2.default)(data.content);
                    // Initialize multiple wishlist for new items
                    newItems.mage('multipleWishlist', {
                        'canCreate': multiplewishlist().can_create,
                        'wishlists': multiplewishlist().short_list,
                        'wishlistLink': '.action.towishlist'
                    });
                }
            }
        }, {
            key: 'progress',
            value: function progress() {
                var self = this;
                this.productsWrapper.loader({ 'icon': self.options.loaderIcon, 'texts': { 'loaderText': self.options.loaderText } }).trigger('processStart');
            }
        }, {
            key: 'success',
            value: function success(data) {
                _get(ViewCustom.prototype.__proto__ || Object.getPrototypeOf(ViewCustom.prototype), 'success', this).call(this, data);
                this._updateButtonContent(data);
                this.productsWrapper.loader().trigger('processStop');
                this._refreshInit(data);
            }
        }, {
            key: 'finish',
            value: function finish() {
                this.productsWrapper.find('.infinitescroll-button').remove();
            }
        }, {
            key: 'reload',
            value: function reload(options) {
                options.buttonContent = this._setButtonContent(options.currentCount, options.totalCount);
                this.unwatch();

                new ViewCustom(options);
            }
        }, {
            key: '_setButtonContent',
            value: function _setButtonContent(currentCount, totalCount) {
                return 'Load more';
            }
        }, {
            key: '_updateButtonContent',
            value: function _updateButtonContent(data) {
                if (this.actionData.vmb) {
                    this.actionData.vmb.buttonState(this._setButtonContent(data.currentCount, data.totalCount));
                }
            }
        }]);

        return ViewCustom;
    }(_index2.default);

    exports.default = ViewCustom;
    module.exports = exports['default'];
});
