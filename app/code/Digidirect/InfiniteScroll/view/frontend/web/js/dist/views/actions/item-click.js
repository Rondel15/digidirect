define(['module', 'exports', 'jquery'], function (module, exports, _jquery) {
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

    var ItemClick = function () {
        function ItemClick(options) {
            _classCallCheck(this, ItemClick);

            this.options = Object.assign({}, this.options, options);
            this.watchers(this.options);
            this.scrollToLastViewedItem();
        }

        _createClass(ItemClick, [{
            key: 'watchers',
            value: function watchers(options) {
                // save URL of clicked item
                (0, _jquery2.default)(options.itemsContainerSelector).on('click', options.itemUrlSelector, function () {
                    var $this = (0, _jquery2.default)(this),
                        itemUrl = $this.attr('href');
                    if (itemUrl) {
                        window.localStorage.setItem(options.itemUrlKey, itemUrl);
                    }
                });
            }
        }, {
            key: 'scrollToLastViewedItem',
            value: function scrollToLastViewedItem() {
                var itemUrl = window.localStorage.getItem(this.options.itemUrlKey),
                    $container;

                if (itemUrl) {
                    $container = this.getChildItemContainer(itemUrl);
                    if ($container.length) {
                        $container[0].scrollIntoView({ behavior: 'smooth' });
                    }
                    window.localStorage.removeItem(this.options.itemUrlKey);
                }
            }
        }, {
            key: 'getChildItemContainer',
            value: function getChildItemContainer(href) {
                return (0, _jquery2.default)(this.options.itemsContainerSelector).find('a[href="' + href + '"]').closest(this.options.itemsContainerSelector + ' ' + this.options.itemSelector);
            }
        }]);

        return ItemClick;
    }();

    exports.default = ItemClick;
    module.exports = exports['default'];
});
