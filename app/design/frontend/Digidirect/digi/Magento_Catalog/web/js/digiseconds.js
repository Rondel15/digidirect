define([
       'ko',
       'uiComponent',
       'Magento_Customer/js/customer-data',
   ], function (ko, Component, customerData) {
       'use strict';
       var subtotalAmount;    
       var percentage;
       return Component.extend({
           initialize: function () {
               this._super();
               this.currentPage = ko.observable(this.currentPage);
               this.categoryName = ko.observable(this.categoryName);
           },
           digiSeconds: function () {
               if (this.currentPage != "catalogsearch_result_index") {
                   if (this.categoryName == "digiSeconds") {
                       jQuery(window).on('resize', function(){
                            var win = jQuery(this);
                            if (win.width() >= 1440) {
                                jQuery('.product-item-info>a').addClass('digiseconds-left-width');
                                jQuery('.testfreaks-items').addClass('digiseconds-left-width');
                                jQuery('.product-reviews-summary').addClass('digiseconds-left-width');
                                jQuery('.product-item-name').addClass('digiseconds-left-width');
                                jQuery('.product-item-details').addClass('digiseconds-left-width');
                                jQuery('.digiseconds-product-details').attr("style", "display:block !important;");
                                jQuery('.digiseconds-savings').attr("style", "display:block !important;");
                                jQuery('.default-product-name').attr("style", "display:none !important;");
                            } else {
                                jQuery('.product-item-info>a').removeClass('digiseconds-left-width');
                                jQuery('.testfreaks-items').removeClass('digiseconds-left-width');
                                jQuery('.product-reviews-summary').removeClass('digiseconds-left-width');
                                jQuery('.product-item-name').removeClass('digiseconds-left-width');
                                jQuery('.product-item-details').removeClass('digiseconds-left-width');
                                jQuery('.digiseconds-product-details').attr("style", "display:none !important;");
                                jQuery('.digiseconds-savings').attr("style", "display:none !important;");
                                jQuery('.default-product-name').attr("style", "display:block !important;");
                            }
                        });
                   }
               }
           }
       });
   });