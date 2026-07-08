/* eslint no-unused-vars: [1] */
var config = {
    map: {
        '*': {
            'quickView': 'Digidirect_QuickView/js/quickview',
            'quickViewAddToCart': 'Digidirect_QuickView/js/quickview-add-to-cart',
            'quickViewPageEvents': 'Digidirect_QuickView/js/quickview-page-events'
        }
    },
    config: {
        mixins: {
            'Magento_MultipleWishlist/js/multiple-wishlist': {
                'Digidirect_QuickView/js/multiple-wishlist-extend': true
            },
            'mage/gallery/gallery': {
                'Digidirect_QuickView/js/gallery-extend': true
            }
        }
    },
    deps: [
        'babelpolyfill'
    ]
};
