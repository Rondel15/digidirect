import $ from 'jquery';
import ko from 'ko';
import {Store, Events} from 'digidirectStoreCheckout';
import 'mCustomScrollbar';
import 'domReady!';

ko.bindingHandlers.vertScrollbarMiniCart = {
    init(el) {
        let element = $(el);

        element.mCustomScrollbar({
            set_width: false, /*optional element width: boolean, pixels, percentage*/
            set_height: false, /*optional element height: boolean, pixels, percentage*/
            horizontalScroll: false, /*scroll horizontally: boolean*/
            scrollInertia: 150, /*scrolling inertia: integer (milliseconds)*/
            mouseWheel: true, /*mousewheel support: boolean*/
            mouseWheelPixels: "auto", /*mousewheel pixels amount: integer, "auto"*/
            autoDraggerLength: true, /*auto-adjust scrollbar dragger length: boolean*/
            autoHideScrollbar: false, /*auto-hide scrollbar when idle*/
            scrollButtons: {
                /*scroll buttons*/
                enable: false, /*scroll buttons support: boolean*/
                scrollType: "continuous", /*scroll buttons scrolling type: "continuous", "pixels"*/
                scrollSpeed: "auto", /*scroll buttons continuous scrolling speed: integer, "auto"*/
                scrollAmount: 80 /*scroll buttons pixels scroll amount: integer (pixels)*/
            },
            advanced: {
                updateOnBrowserResize: true, /*update scrollbars on browser resize (for layouts based on percentages): boolean*/
                updateOnContentResize: true, /*auto-update scrollbars on content resize (for dynamic content): boolean*/
                autoExpandHorizontalScroll: false, /*auto-expand width for horizontal scrolling: boolean*/
                autoScrollOnFocus: true, /*auto-scroll on focused elements: boolean*/
                normalizeMouseWheelDelta: false /*normalize mouse-wheel delta (-1/1)*/
            },
            contentTouchScroll: true, /*scrolling by touch-swipe content: boolean*/
            callbacks: {
                /*user custom callback function on scroll start event*/
                onScrollStart: function () {
                }, /*user custom callback function on scroll event*/
                onScroll: function () {
                }, /*user custom callback function on scroll end reached event*/
                onTotalScroll: function () {
                }, /*user custom callback function on scroll begin reached event*/
                onTotalScrollBack: function () {
                }, /*user custom callback function on scrolling event*/
                whileScrolling: function () {
                },
                onTotalScrollOffset: 0, /*scroll end reached offset: integer (pixels)*/
                onTotalScrollBackOffset: 0 /*scroll begin reached offset: integer (pixels)*/
            },
            theme: "dark-3"
        });
    }
};

const MIXIN = {
    options: {
        minicartWrapperSelector: '.minicart-wrapper',
        minicartWrapperSidebarWidgetName: 'mageSidebar',
    },
    initialize() {
        this._super();
        this.Store = Store;
        this.Events = Events;
    },

    /**
     *  Get summary cart count in custom format
     * @returns string `(${SUMMARY_COUNT} item/items)`
     */
    getFormattedSummaryCount() {
        return ko.pureComputed(() => {
            return +this.getCartParam('summary_count') === 1
                ? `(${this.getCartParam('summary_count')} ${$.mage.__('item')})`
                : `(${this.getCartParam('summary_count')} ${$.mage.__('items')})`;
        });
    },

    /**
     * Close mini shopping cart.
     */
    closeMinicart() {
        this.Store.emit(this.Events.MINICART_CLOSE);
    },
    closeSidebar: function () {
        this.Store.emit(this.Events.MINICART_CLOSE);
    }
};

export default target => target.extend(MIXIN);
