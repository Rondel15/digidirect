define([
    'jquery',
    'ko',
    'uiComponent',
    'Digidirect_Locator/js/model/locations',
    'Magento_Ui/js/lib/core/events'
], function ($, ko, Component, locations, events) {
    'use strict';

    return Component.extend({
        defaults: {
            isPaginationEnable: true,
            defaultPerPage: 10,
            pageFrame: 5,
            pageJump: 2,
            modules: {
                details: 'locator_details'
            }
        },
        initialize: function () {
            this._super();
            this.renderItems();
            this.toggleStoreListDisplay();
            console.log("Click & Collect initialize! app/design");
        },
        renderItems: function () {
            if (this.isPaginationEnable) {
                this.pageFrame--;
                this.paginationObservable();
            } else {
                this.perPage = function () {
                    return this.defaultPerPage;
                };
                this.locationsList = locations.items;
            }
            console.log("Click & Collect renderItems()! app/design");
            
        },
        toggleStoreListDisplay: function () {
            
            $(document).on('click', '.collect-block .link.action.primary', function () {
                $(".store-locator-wrapper").removeAttr("style");
                $(".store-locator-wrapper").attr("style", "display:block !important;");
            });
            
            $(document).on('click', '.locator-items button.action.-select', function () {
                $(".store-locator-wrapper").removeAttr("style");
                $(".store-locator-wrapper").attr("style", "display:none !important;");
            });
            console.log("Click & Collect toggleStoreListDisplay()! app/design");
        },
        testDataBind: function () {
            $(document.getElementsByClassName('mCustomScrollBox')[0]).ready(function(){
                //alert("#pickup-available length is " + $('#pickup-available .collectlocator-wrapper .mCustomScrollBox').length);
                //alert("#pickup-unavailable length is " + $('#pickup-unavailable .collectlocator-wrapper .mCustomScrollBox').length);
            });
        },
        paginationObservable: function () {
            var self = this;

            self.frameStart = ko.observable(1);
            self.frameEnd = ko.observable(1 + this.pageFrame);

            self.perPage = ko.computed(function () {
                if (locations.settings() !== undefined && locations.settings().stores_on_locator_page) {
                    return locations.settings().stores_on_locator_page;
                }
                return self.defaultPerPage;
            });

            self.locationsList = ko.computed(function () {
                var startIndex = self.currentPage() === 1 ? 0 : (self.currentPage() - 1) * self.perPage();
                return locations.items().slice(startIndex, startIndex + self.perPage());
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

            self.hasPrevPage = ko.computed(function () {
                return self.currentPage() > 1;
            });

            self.pager = ko.computed(function () {
                var pagesArray = [],
                    i,
                    pageCount = self.lastPage(),
                    from = Math.max(1, self.currentPage() - self.getFrameStart()),
                    to = Math.min(pageCount, self.currentPage() + self.getFrameEnd()),
                    pageFrom = Math.max(1, Math.min(to - self.pageFrame, from)),
                    pageTo = Math.min(pageCount, Math.max(from + self.pageFrame, to));

                self.frameStart(pageFrom);
                self.frameEnd(pageTo);

                for (i = pageFrom; i <= pageTo; i++) {
                    pagesArray.push(i);
                }

                return pagesArray;
            });

            self.canShowPreviousJump = ko.computed(function () {
                return self.getPreviousJumpPage() !== null;
            });

            self.canShowNextJump = ko.computed(function () {
                return self.getNextJumpPage() !== null;
            });
            
            this.locationsListAvailable = $('.store-locator-wrapper').length;
            this.locationsListUnavailable = $('.store-section').length;
                      
        },
        getFrameStart: function () {
            return Math.floor(this.pageFrame / 2);
        },
        getFrameEnd: function () {
            return Math.ceil(this.pageFrame / 2);
        },
        currentPage: locations.currentPage,
        locations: locations,
        prevItem: function () {
            this.currentPage(this.currentPage() - 1);
        },
        nextItem: function () {
            this.currentPage(this.currentPage() + 1);
        },
        isCurrentPage: function (item) {
            return item === this.currentPage();
        },
        goToPage: function (page) {
            this.currentPage(page);
        },
        getJump: function () {
            return parseInt(this.pageJump, 10);
        },
        canShowFirst: function () {
            return this.getJump() > 1 && this.frameStart() > 1;
        },
        canShowLast: function () {
            return this.getJump() > 1 && this.frameEnd() < this.lastPage();
        },
        getPreviousJumpPage: function () {
            if (!this.getJump()) {
                return null;
            }

            var $frameStart = this.frameStart();
            if ($frameStart - 1 > 1) {
                return Math.max(2, $frameStart - this.getJump());
            }

            return null;
        },
        getNextJumpPage: function () {
            if (!this.getJump()) {
                return null;
            }

            var $frameEnd = this.frameEnd();
            if (this.lastPage() - $frameEnd > 1) {
                return Math.min(this.lastPage() - 1, $frameEnd + this.getJump());
            }

            return null;
        },
        showDetails: function (location, e) {
//            if (!locations.settings().open_in_popup) return true;
//
//            e.preventDefault();

            console.log("click");
            this.details().location = location;
            events.trigger('location.show', location, locations.settings());
        },
        onRenderList: function () {

            console.log("onrenderlist");

            const targetNode = document.querySelector(".store-locator-wrapper");

            if (targetNode) {
                const observer = new MutationObserver((mutationsList) => {
                    var pickUpAvailable;
                    var pickUpUnavailable;

                    pickUpAvailable = $('.pickup-available .collectlocator-wrapper .mCustomScrollBox .locator-item').length;
                    pickUpUnavailable = $('.pickup-unavailable .collectlocator-wrapper .mCustomScrollBox .locator-item').length;

                    if (pickUpAvailable == 0 && pickUpUnavailable > 0) {
                        $('.pickup-available').hide();
                        $('.pickup-unavailable').show();

                        $('.pickup-unavailable .title-cc').hide();
                        $('.pickup-unavailable .description-cc').hide();
                        $('.title-cc-unavailable').show();
                        $('.description-cc-unavailable').show();

                    } else if (pickUpAvailable > 0 && pickUpUnavailable == 0) {
                        $('.pickup-available').show();
                        $('.pickup-unavailable').hide();

                        $('.pickup-unavailable .title-cc').show();
                        $('.pickup-unavailable .description-cc').show();
                        $('.title-cc-unavailable').hide();
                        $('.description-cc-unavailable').hide();

                    } else if (pickUpAvailable == 0 && pickUpUnavailable == 0) {
                        $('.pickup-available').hide();
                        $('.pickup-unavailable').show();

                        $('.pickup-unavailable .title-cc').hide();
                        $('.pickup-unavailable .description-cc').hide();
                        $('.title-cc-unavailable').show();
                        $('.description-cc-unavailable').show();

                    } else if (pickUpAvailable > 0 && pickUpUnavailable > 0) {
                        $('.pickup-available').show();
                        $('.pickup-unavailable').show();

                        $('.pickup-unavailable .title-cc').show();
                        $('.pickup-unavailable .description-cc').show();
                        $('.title-cc-unavailable').hide();
                        $('.description-cc-unavailable').hide();

                    }

                    // console.log('pickUpAvailable : ' + pickUpAvailable);
                    // console.log('pickUpUnavailable : ' + pickUpUnavailable);

                });

                const config = { childList: true, subtree: true }; // Monitor changes to child elements

                observer.observe(targetNode, config);
            }

            // $(".store-locator-wrapper").bind("DOMSubtreeModified", function() {
            //
            //     var pickUpAvailable;
            //     var pickUpUnavailable;
            //
            //     pickUpAvailable = $('.pickup-available .collectlocator-wrapper .mCustomScrollBox .locator-item').length;
            //     pickUpUnavailable = $('.pickup-unavailable .collectlocator-wrapper .mCustomScrollBox .locator-item').length;
            //
            //     if (pickUpAvailable == 0 && pickUpUnavailable > 0) {
            //         $('.pickup-available').hide();
            //         $('.pickup-unavailable').show();
            //
            //         $('.pickup-unavailable .title-cc').hide();
            //         $('.pickup-unavailable .description-cc').hide();
            //         $('.title-cc-unavailable').show();
            //         $('.description-cc-unavailable').show();
            //
            //     } else if (pickUpAvailable > 0 && pickUpUnavailable == 0) {
            //         $('.pickup-available').show();
            //         $('.pickup-unavailable').hide();
            //
            //         $('.pickup-unavailable .title-cc').show();
            //         $('.pickup-unavailable .description-cc').show();
            //         $('.title-cc-unavailable').hide();
            //         $('.description-cc-unavailable').hide();
            //
            //     } else if (pickUpAvailable == 0 && pickUpUnavailable == 0) {
            //         $('.pickup-available').hide();
            //         $('.pickup-unavailable').show();
            //
            //         $('.pickup-unavailable .title-cc').hide();
            //         $('.pickup-unavailable .description-cc').hide();
            //         $('.title-cc-unavailable').show();
            //         $('.description-cc-unavailable').show();
            //
            //     } else if (pickUpAvailable > 0 && pickUpUnavailable > 0) {
            //         $('.pickup-available').show();
            //         $('.pickup-unavailable').show();
            //
            //         $('.pickup-unavailable .title-cc').show();
            //         $('.pickup-unavailable .description-cc').show();
            //         $('.title-cc-unavailable').hide();
            //         $('.description-cc-unavailable').hide();
            //
            //     }
            //
            //     console.log('pickUpAvailable : ' + pickUpAvailable);
            //     console.log('pickUpUnavailable : ' + pickUpUnavailable);
            //
            // });

        }
    });
});
