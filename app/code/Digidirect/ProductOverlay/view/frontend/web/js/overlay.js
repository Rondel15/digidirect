define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.productOverlay', {
        options: {
            'size': 20,
            'path': '.product-image-container',
            'mode': 'cat',
            'productGridSelector': '.product-item',
            'hideForConfigurable': false,
            'swatchContainer': '[data-role=swatch-options]',
            'overlaySelector': ''
        },
        image: null,
        imageWidth: null,
        imageHeight: null,
        parent: null,

        _create: function () {
            this.element = $(this.element);
            this.image = this.element.find('.img');
            this.parent = this.element.parent();

            var newParent,
                me;

            // move overlay to container from settings
            if (this.options.path && this.options.path !== '') {
                newParent = this.element.closest(this.options.productGridSelector).find(this.options.path);
                if (newParent.length) {
                    //this.parent = newParent;
                    //newParent.append(this.element);
                } else {
                    this.element.addClass('-hide');
                }
            }

            // required for child position absolute
            this.parent.css('position', 'relative');

            // observe zoom load event for moving overlay
            this.productPageZoomEvent();

            /* get default image size */
            if (this.imageLoaded(this.image)) {
                me = this;
                this.image.load(function () {
                    me.element.addClass('-ready');
                    me.imageWidth = this.naturalWidth;
                    me.imageHeight = this.naturalHeight;
                    me.setOverlayStyle();
                });
            } else {
                if (this.image[0].naturalWidth === 0 || this.image[0].naturalHeight === 0) {
                    this.image.on('load', $.proxy(function () {
                        this.setImageProps();
                    }, this));
                } else {
                    this.setImageProps();
                }
            }
        },

        imageLoaded: function (img) {
            if (!img.complete) {
                return false;
            }

            if (typeof img.naturalWidth !== 'undefined' && img.naturalWidth === 0) {
                return false;
            }

            return true;
        },

        setImageProps: function () {
            this.element.addClass('-ready');
            this.imageWidth = this.image[0].naturalWidth;
            this.imageHeight = this.image[0].naturalHeight;
            this.setOverlayStyle();
        },

        productPageZoomEvent: function () {
            if (this.options.mode === 'prod') {
                var self = this,
                    swatchMode = !!$(self.options.swatchContainer).length;

                $(document).on('fotorama:load', function (event) {
                    if (self && self.options.path && self.options.path !== '') {
                        var newParent = $(self.options.path);
                        if (newParent.length && !self.isExistInParent(newParent)) {
                            self.parent = newParent;
                            //newParent.append(self.element);
                            if (!(newParent.attr('class').indexOf('fotorama') > -1)) {
                                newParent.css('position', 'relative');
                            }
                            if (!swatchMode) {
                                newParent.find(self.options.overlaySelector).removeClass('-hide');
                            }
                            $(self.options.swatchContainer).trigger('product.overlay.appended');
                        }
                    }
                });

                $(window).resize(function () {
                    self.setOverlayStyle();
                });
            }
        },

        isExistInParent: function (parent) {
            return !!parent.find(this.options.overlaySelector).length;
        },

        setOverlayStyle: function () {
            var parentWidth = parseInt(this.parent.css('width').replace(/\D+/g, '')),
                tmpWidth,
                tmpHeight;

            // get block size depend settings
            if (this.options.size) {
                parentWidth = parseInt(this.parent.css('width').replace(/\D+/g, ''));
                if (parentWidth && this.options.size > 0) {
                    this.imageWidth = parentWidth * this.options.size / 100;
                }
            } else {
                this.imageWidth = this.imageWidth + 'px';
            }
            this.element.css({'width': this.imageWidth});
            this.imageHeight = this.image.height();

            // if container doesn't load(height = 0 ) set proportional height
            if (!this.imageHeight && this.image[0].naturalWidth !== 0) {
                tmpWidth = this.image[0].naturalWidth;
                tmpHeight = this.image[0].naturalHeight;
                this.imageHeight = this.imageWidth * (tmpHeight / tmpWidth);
            }

            // for whole block
            this.element.css({
                'height': this.imageHeight + 'px'
            });

            this.element.on('click', function () {
                $(this).parent().trigger('click');
            });

            // hide overlay if it does not have use for parent flag
            if (this.options.hideForConfigurable) {
//                this.element.addClass('-hide');

                console.log("why hiding");
            }
        }
    });

    return $.digidirect.productOverlay;
});
