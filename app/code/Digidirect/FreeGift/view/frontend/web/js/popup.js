define([
    "jquery",
    "slickCarousel",
    "jquery/ui",
    "Digidirect_FreeGift/js/type/configurable"
], function ($) {

    $.widget('mage.freegiftPopup', {
        options: {
            autoOpen: false,
            slickSettings: {}
        },

        isSlickInitialized: false,

        _create: function () {
            $(this.element).mousedown($.proxy(function (event) {
                if ($(event.target).data('role') == 'freegift-overlay') {
                    event.stopPropagation();
                    this.hide();
                }
            }, this));

            $('[data-role=freegift-popup-hide]').click($.proxy(this.hide, this));

            if (this.options.autoOpen) {
                this.show();
            }
        },

        hide: function () {
            $(this.element).removeClass('-open');
            $('body').removeClass('-freegift-popup-opened');
        },

        show: function () {
            if (!this.isSlickInitialized) {
                var forms = $('.freegift_items_form'),
                    length = forms.length;

                $(this.element).addClass('-open');

                for (var i = 0; i < length; i += 1) {
                    var overlayConfig = $(forms[i]).data('mageFreegiftConfigurable'),
                        freeGiftGallery = $(forms[i]).children('[data-role=freegift-gallery]');

                    if (overlayConfig) {
                        freeGiftGallery.slick(this.options.slickSettings);

                        var images = overlayConfig.options.spConfig.images,
                            imgConfig;
                        for (var j in images) {
                            imgConfig = images[j][0];
                            freeGiftGallery.slick('slickAdd', '<div class="item" data-index =' + j + '><img src = ' + imgConfig.cart + ' />' + '</div>');
                        }
                    }
                }

                $(this.element).removeClass('-open');

                this.isSlickInitialized = true;
            }

            $(this.element).addClass('-open');
            $('body').addClass('-freegift-popup-opened');
        }
    });

    return $.mage.freegiftPopup;
});
