define(['jquery', 'jquery/ui', 'domReady!', 'catalogAddToCart', 'truncateCollection'], function (_jquery) {
    'use strict';

    var _jquery2 = _interopRequireDefault(_jquery);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    var PRODUCT_TILE_DEFAULT = 'default';
    var PRODUCT_TILE_SLIDER = 'slider';
    var PRODUCT_TILE_FILTERED_SLIDER = 'filteredSlider';

    var PRODUCT_TILE_TYPES = [PRODUCT_TILE_DEFAULT, PRODUCT_TILE_SLIDER, PRODUCT_TILE_FILTERED_SLIDER];

    _jquery2.default.widget('digidirect.productTile', {
        version: '0.0.2',
        options: {
            type: 'default',
            slickConfig: {
                infinite: false,
                slidesToShow: 2,
                slidesToScroll: 2,
                mobileFirst: true,
                arrows: false,
                responsive: [{
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                }, {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4,
                        arrows: true
                    }
                }, {
                    breakpoint: 1439,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5,
                        arrows: true
                    }
                }]
            },
            isRedirectToCartEnabled: false,
            isTruncateProductsName: true,
            truncateCollectionConfig: {},
            slickFilterConfig: {},
            addToCartFormSelector: '[data-role=tocart-form]',
            catalogPriceRuleModalTriggerSelector: '[data-rule-id]'
        },
        _create: function _create() {
            if (!this._checkProductTileType()) {
                return console.error('Undefined product tile type');
            }

            switch (this.options.type) {
                case PRODUCT_TILE_SLIDER:
//                    this.initSlider();
                    break;
                case PRODUCT_TILE_FILTERED_SLIDER:
                    this.initFilteredSlider();
                    break;
                default:
                    this.initAsyncAddToCart().truncateProductsName();
            }
        },
        _checkProductTileType: function _checkProductTileType() {
            return PRODUCT_TILE_TYPES.indexOf(this.options.type) !== -1;
        },
        initSlider: function initSlider() {
            var _this = this;

//            require(['jquery', 'jquery/ui', 'slickInit', 'catalogPriceRuleModal'], function ($) {
//                $(_this.element).on('init', function () {
//                    _this.initAsyncAddToCart().initPriceRuleModal().truncateProductsName();
//                }).slickInit(_this.options.slickConfig);
//            });

            return this;
        },
        initFilteredSlider: function initFilteredSlider() {
            var _this2 = this;

//            require(['jquery', 'jquery/ui', 'slickFilter', 'catalogPriceRuleModal'], function ($) {
//                $(_this2.element).on('init', function () {
//                    _this2.initAsyncAddToCart().initPriceRuleModal().truncateProductsName();
//                }).slickFilterInit(_this2.options.slickFilterConfig);
//            });

            return this;
        },
        initAsyncAddToCart: function initAsyncAddToCart() {
            if (this.options.isRedirectToCartEnabled) {
                return this;
            }
            this.element.find(this.options.addToCartFormSelector).catalogAddToCart();

            return this;
        },
        initPriceRuleModal: function initPriceRuleModal() {
            var currentTriggerElement = this.element.find(this.options.catalogPriceRuleModalTriggerSelector),
                ruleId = currentTriggerElement.data('rule-id');

            if (ruleId) {
                currentTriggerElement.catalogPriceRuleModal({
                    'ruleId': currentTriggerElement.data('rule-id')
                });
            }

            return this;
        },
        truncateProductsName: function truncateProductsName() {
            if (!this.options.isTruncateProductsName) {
                return this;
            }
            this.element.truncateCollection(this.options.truncateCollectionConfig);

            return this;
        }
    });
});
