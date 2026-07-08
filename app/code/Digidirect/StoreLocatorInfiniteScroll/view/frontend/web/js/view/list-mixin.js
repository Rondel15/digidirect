define([
    'jquery',
    'ko',
    'uiComponent',
    'Digidirect_Locator/js/model/locations'
], function ($, ko, Component, locations) {
    'use strict';

    var mixin = {
        defaults: {
            infiniteOptions: window.storelocatorinfinitescroll || false,
            scrollContainer: 'window',
            scrollOffset: 150
        },
        renderItems: function () {
            if (window.storelocatorinfinitescroll) {
                this.infiniteObservable(window.storelocatorinfinitescroll);
            } else {
                this._super();
            }
        },
        infiniteObservable: function (options) {
            var self = this;

            self.perPage = ko.computed(function () {
                if (options.perPageCount) {
                    return options.perPageCount;
                }
                if (locations.settings() !== undefined && locations.settings().stores_on_locator_page) {
                    return locations.settings().stores_on_locator_page;
                }
                return self.defaultPerPage;
            });

            self.locationsList = ko.computed(function () {
                return locations.items().slice(0, self.currentPage() * self.perPage());
            });

            self.totalItemCount = ko.computed(function () {
                return locations.items().length;
            });

            self.lastPage = ko.computed(function () {
                return Math.floor((self.totalItemCount() - 1) / self.perPage()) + 1;
            });

            self.hasNextPage = ko.computed(function () {
                return self.currentPage() < self.lastPage();
            });

            self.pager = function () {
                return [];
            };
        },
        onClick: function () {
            this.currentPage(this.currentPage() + 1);
        },
        onRenderList: function () {
            this._super();

            if (window.storelocatorinfinitescroll && window.storelocatorinfinitescroll.action === 'scroll') {
                this._initScroll();
            }
        },
        _initScroll: function () {
            var self = this,
                $container = this._isWindowContainer(this.scrollContainer) ? $(window) : $(this.scrollContainer);

            $container.off('scroll');
            $container.on('scroll', function () {
                if (self._loadOnScroll()) {
                    self.currentPage(self.currentPage() + 1);
                }
            });

            if (this._preFill()) {
                self.currentPage(self.currentPage() + 1);
            }
        },
        /**
         * Current scroll position of window
         * @returns {number|Number}
         * @private
         */
        _getPosition: function () {
            return document.documentElement.scrollTop || document.body.scrollTop || window.pageYOffset;
        },
        /**
         * Get Threshold that indicate start loading the next data
         * @returns {*}
         * @private
         */
        _getScrollThreshold: function () {
            var $lastElement,
                scrollOffset = this.scrollOffset;

            scrollOffset = (scrollOffset >= 0 ? scrollOffset * -1 : scrollOffset);

            $lastElement = $(this.itemsContainerSelector).find(this.itemSelector).last();

            // if the don't have a last element, the DOM might not have been loaded,
            // or the selector is invalid
            if ($lastElement.length === 0) {
                return;
            }

            return ($lastElement.offset().top + $lastElement.height() + scrollOffset);
        },
        /**
         * Returns current scroll offset of scrollContainer
         * @returns {*}
         * @private
         */
        _getCurrentScrollOffset: function () {
            var scrollTop = 0,
                $container = $(this.scrollContainer),
                containerHeight = $container.height();

            if (this._isWindowContainer(this.scrollContainer)) {
                scrollTop = this._getPosition();
                containerHeight = document.documentElement.clientHeight;
            } else {
                scrollTop = $container.offset().top;
            }

            return (scrollTop + containerHeight);
        },
        /**
         * Check scroll bar of scrollContainer
         * @returns {boolean}
         * @private
         */
        _hasScrollBar: function () {
            var $container = $(this.scrollContainer);

            if (this._isWindowContainer(this.scrollContainer)) {
                $container = $('body');
            }
            return $container.get(0).scrollHeight > $container.height();
        },
        /**
         * The container is ready for loading the next page
         * @returns {boolean}
         * @private
         */
        _loadOnScroll: function () {
            var currentScrollOffset = this._getCurrentScrollOffset(),
                scrollThreshold = this._getScrollThreshold();

            return currentScrollOffset >= scrollThreshold;
        },
        /**
         * Scroll container is window
         * @param container
         * @returns {boolean}
         * @private
         */
        _isWindowContainer: function (container) {
            return container === 'window';
        },
        /**
         * Pre fill data
         * @returns {boolean}
         * @private
         */
        _preFill: function () {
            return (this.preFill && !this._hasScrollBar());
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
