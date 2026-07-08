define(['module', 'exports', 'jquery', 'ko', 'digidirectStoreCheckout', 'mCustomScrollbar', 'domReady!'], function (module, exports, _jquery, _ko, _digidirectStoreCheckout) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _ko2 = _interopRequireDefault(_ko);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    _ko2.default.bindingHandlers.vertScrollbarMiniCart = {
        init: function init(el) {
            var element = (0, _jquery2.default)(el);

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
                    onScrollStart: function onScrollStart() {}, /*user custom callback function on scroll event*/
                    onScroll: function onScroll() {}, /*user custom callback function on scroll end reached event*/
                    onTotalScroll: function onTotalScroll() {}, /*user custom callback function on scroll begin reached event*/
                    onTotalScrollBack: function onTotalScrollBack() {}, /*user custom callback function on scrolling event*/
                    whileScrolling: function whileScrolling() {},
                    onTotalScrollOffset: 0, /*scroll end reached offset: integer (pixels)*/
                    onTotalScrollBackOffset: 0 /*scroll begin reached offset: integer (pixels)*/
                },
                theme: "dark-3"
            });
        }
    };

    var MIXIN = {
        options: {
            minicartWrapperSelector: '.minicart-wrapper',
            minicartWrapperSidebarWidgetName: 'mageSidebar'
        },
        initialize: function initialize() {
            this._super();
            this.Store = _digidirectStoreCheckout.Store;
            this.Events = _digidirectStoreCheckout.Events;
        },
        getFormattedSummaryCount: function getFormattedSummaryCount() {
            var _this = this;

            return _ko2.default.pureComputed(function () {
                
                if(_this.getCartParam('summary_count') == undefined){
                    return "";
                }
                
                return + _this.getCartParam('summary_count') === 1 ? _jquery2.default.mage.__('Total Items =') + ' ' + _this.getCartParam('summary_count') : _jquery2.default.mage.__('Total Items =') + ' ' + _this.getCartParam('summary_count');
            });
        },
        closeMinicart: function closeMinicart() {
            this.Store.emit(this.Events.MINICART_CLOSE);
        },

        closeSidebar: function closeSidebar() {
            this.Store.emit(this.Events.MINICART_CLOSE);
        }
    };

    exports.default = function (target) {
        return target.extend(MIXIN);
    };

    module.exports = exports['default'];
    
});
