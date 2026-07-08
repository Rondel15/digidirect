import $ from 'jquery';
import 'jquery/ui';
import 'domReady!';
import 'catalogAddToCart';
import 'truncateCollection';

const PRODUCT_TILE_DEFAULT = 'default';
const PRODUCT_TILE_SLIDER = 'slider';
const PRODUCT_TILE_FILTERED_SLIDER = 'filteredSlider';

const PRODUCT_TILE_TYPES = [
    PRODUCT_TILE_DEFAULT,
    PRODUCT_TILE_SLIDER,
    PRODUCT_TILE_FILTERED_SLIDER
];

$.widget('digidirect.productTile', {
    version: '0.0.2',
    options: {
        type: 'default',
        slickConfig: {
            infinite: false,
            slidesToShow: 2,
            slidesToScroll: 2,
            mobileFirst: true,
            arrows: false,
            responsive: [
                {
                    breakpoint: 767,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: 4,
                        slidesToScroll: 4,
                        arrows: true
                    }
                },
                {
                    breakpoint: 1439,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5,
                        arrows: true
                    }
                }
            ]
        },
        isRedirectToCartEnabled: false,
        isTruncateProductsName: true,
        truncateCollectionConfig: {},
        slickFilterConfig: {},
        addToCartFormSelector: '[data-role=tocart-form]',
        catalogPriceRuleModalTriggerSelector: '[data-rule-id]'
    },
    _create() {
        if (!this._checkProductTileType()) {
            return console.error('Undefined product tile type');
        }

        switch (this.options.type) {
            case PRODUCT_TILE_SLIDER:
                this.initSlider();
                break;
            case PRODUCT_TILE_FILTERED_SLIDER:
                this.initFilteredSlider();
                break;
            default:
                this.initAsyncAddToCart()
                    .truncateProductsName();
        }
    },
    _checkProductTileType() {
        return PRODUCT_TILE_TYPES.indexOf(this.options.type) !== -1;
    },
    initSlider() {
//        require(['jquery', 'jquery/ui', 'slickInit', 'catalogPriceRuleModal'], ($) => {
//            $(this.element).on('init', () => {
//                this.initAsyncAddToCart()
//                    .initPriceRuleModal()
//                    .truncateProductsName();
//            }).slickInit(this.options.slickConfig);
//        });

        return this;
    },
    initFilteredSlider() {
//        require(['jquery', 'jquery/ui', 'slickFilter', 'catalogPriceRuleModal'], ($) => {
//            $(this.element).on('init', () => {
//                this.initAsyncAddToCart()
//                    .initPriceRuleModal()
//                    .truncateProductsName();
//            }).slickFilterInit(this.options.slickFilterConfig);
//        });

        return this;
    },
    initAsyncAddToCart() {
        if (this.options.isRedirectToCartEnabled) {
            return this;
        }
        this.element.find(this.options.addToCartFormSelector).catalogAddToCart();

        return this;
    },
    initPriceRuleModal() {
        let currentTriggerElement = this.element.find(this.options.catalogPriceRuleModalTriggerSelector),
            ruleId = currentTriggerElement.data('rule-id');

        if (ruleId) {
            currentTriggerElement.catalogPriceRuleModal({
                'ruleId': currentTriggerElement.data('rule-id')
            });
        }

        return this;
    },
    truncateProductsName() {
        if (!this.options.isTruncateProductsName) {
            return this;
        }
        this.element.truncateCollection(this.options.truncateCollectionConfig);

        return this;
    }
});
