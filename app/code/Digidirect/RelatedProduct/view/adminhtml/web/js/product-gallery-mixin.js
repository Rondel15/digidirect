/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint jquery:true*/
define([
    'jquery'
], function ($) {

    return function (target) {

        // Extension for mage.productGallery - add updates for Feature Brand
        $.widget('mage.productGallery', target, { // target is default productGallery widget
            /**
             * Bind handler to elements
             * @protected
             */
            _bind: function () {
                this._super();

                this._on({updateFeatureBrand: '_updateFeatureBrand'});
            },

            /**
             * Initializes dialog element.
             */
            _initDialog: function () {
                var $dialog;
                this._super();

                $dialog = this.$dialog;

                $dialog.on('change', '[data-role=feature-brand-trigger]', $.proxy(function (e) {
                    var imageData = $dialog.data('imageData');

                    this.element.trigger('updateFeatureBrand', {
                        featured_product_image: $(e.currentTarget).is(':checked'),
                        imageData: imageData
                    })
                }, this));

                this.$dialog = $dialog;
            },


            /**
             * Change feature brand
             *
             * @param event
             * @param data
             * @private
             */
            _updateFeatureBrand: function (event, data) {
                var imageData = data.imageData,
                    featured_product_image = +data.featured_product_image,
                    $imageContainer = this.findElement(imageData);

                $imageContainer.find('[name*="featured_product_image"]').val(featured_product_image);
                imageData.featured_product_image = featured_product_image;

                this._contentUpdated();
            }
        });

        return $.mage.productGallery;
    };
});