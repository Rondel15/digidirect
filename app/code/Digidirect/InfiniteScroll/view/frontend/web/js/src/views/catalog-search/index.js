import $ from 'jquery';
import View from './../index';
import ButtonCatalog from 'text!Digidirect_InfiniteScroll/template/catalog-search/button.html';
import Amount from './../actions/update-amount';
import customerData from 'Magento_Customer/js/customer-data';

export default class ViewCustom extends View {
    constructor (options) {
        options.buttonTemplate = ButtonCatalog;
        super(options);
        this.productsWrapper = $('.products.wrapper');

        if (this.options.rememberScrollState) {
            this._restoreCatalogState();
        }
    }

    /**
     * Restore previously saved scroll state
     * @private
     */
    _restoreCatalogState () {
        var state = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey));

        if (state && window.location.href === state.location && window.localStorage.getItem(this.options.itemUrlKey)) {
            this._refreshInit(state);
        }
    }

    /**
     * Reinitialize events on newly added items
     * @private
     */
    _refreshInit (data) {
        new Amount(data.perPageCount, data.currentCount, data.totalCount);
        this.productsWrapper.find('[data-role=tocart-form], .form.map.checkout').catalogAddToCart();

        if ($('#form-tmpl-multiple').length) {
            let multiplewishlist = customerData.get('multiplewishlist'),
                newItems = $(data.content);
            // Initialize multiple wishlist for new items
            newItems.mage('multipleWishlist', {
                'canCreate': multiplewishlist().can_create,
                'wishlists': multiplewishlist().short_list,
                'wishlistLink': '.action.towishlist'
            });
        }
    }

    progress () {
        super.progress();
        var self = this;
        this.productsWrapper.loader({'icon': self.options.loaderIcon, 'texts': {'loaderText': self.options.loaderText}}).trigger('processStart');
    }

    success (data) {
        super.success(data);
        this.productsWrapper.loader().trigger('processStop');
        this._refreshInit(data);
    }

    finish () {
        this.productsWrapper.find('.infinitescroll-button').remove();
    }

    reload (options) {
        this.unwatch();
        new ViewCustom(options);
    }
}
