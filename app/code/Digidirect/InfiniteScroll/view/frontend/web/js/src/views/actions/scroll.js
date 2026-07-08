import $ from 'jquery';
import {Store, Events} from './../../common/store';
import ItemClick from './item-click';

export default class Action {
    constructor (options, view) {
        this.options = Object.assign({}, this.options, options);
        this._initScroll(this.options, view);
    }

    /**
     * Initialize scroll
     * @param options
     * @param view
     * @private
     */
    _initScroll (options, view) {
        var self = this;

        if (this.options.scrollToLastViewedItem) {
            new ItemClick(options);
        }

        $(view.options.scrollContainer).addClass('-scroll').on('scroll', () => {
            if (self._loadOnScroll()) {
                Store.emit(Events.DATA_FETCH_START, view.options.nextUrl);
            }
        });

        if (this._preFill()) {
            Store.emit(Events.DATA_FETCH_START, view.options.nextUrl);
        }
    }
    /**
     * Current scroll position of window
     * @returns {number|Number}
     * @private
     */
    _getPosition () {
        return document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
    }
    /**
     * Get Threshold that indicate start loading the next data
     * @returns {*}
     * @private
     */
    _getScrollThreshold () {
        var $lastElement,
            scrollOffset = this.options.scrollOffset;

        scrollOffset = (scrollOffset >= 0 ? scrollOffset * -1 : scrollOffset);

        $lastElement = $(this.options.itemsContainerSelector).find(this.options.itemSelector).last();

        // if the don't have a last element, the DOM might not have been loaded,
        // or the selector is invalid
        if ($lastElement.length === 0) {
            return;
        }

        return ($lastElement.offset().top + $lastElement.height() + scrollOffset);
    }
    /**
     * Returns current scroll offset of scrollContainer
     * @returns {*}
     * @private
     */
    _getCurrentScrollOffset () {
        var scrollTop = 0,
            $container = $(this.options.scrollContainer),
            containerHeight = $container.height();

        if (this._isWindowContainer($container)) {
            scrollTop = this._getPosition();
            containerHeight = document.documentElement.clientHeight;
        } else {
            scrollTop = $container.offset().top;
        }

        return (scrollTop + containerHeight);
    }
    /**
     * Check scroll bar of scrollContainer
     * @returns {boolean}
     * @private
     */
    _hasScrollBar () {
        var $container = $(this.options.scrollContainer);

        if (this._isWindowContainer($container)) {
            $container = $('body');
        }
        return $container.get(0).scrollHeight > $container.height();
    }
    /**
     * The container is ready for loading the next page
     * @returns {boolean}
     * @private
     */
    _loadOnScroll () {
        var currentScrollOffset = this._getCurrentScrollOffset(),
            scrollThreshold = this._getScrollThreshold();

        return currentScrollOffset >= scrollThreshold;
    }
    /**
     * Scroll container is window
     * @param container
     * @returns {boolean}
     * @private
     */
    _isWindowContainer (container) {
        return (window === container.get(0));
    }
    /**
     * Pre fill data
     * @returns {boolean}
     * @private
     */
    _preFill () {
        return (this.options.preFill && !this._hasScrollBar());
    }
}
