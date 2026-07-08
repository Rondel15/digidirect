define([
    'jquery',
    'matchMedia',
    'domReady!',
    'jquery/ui',
    'slickInit',
    'redirectUrl',
    'Digidirect_ProductOverlay/js/overlay',
    'Magento_Catalog/js/upsell-products'
], function ($) {
    'use strict';

    /**
     * Fix for random jumping sizes of overlays that are inited on
     * second slider on a page (upsell - You may also Like Slider)
     *
     * The hardcode fix - is to reinit Slickslider and overlays
     * to divide more then two slider init
     */

    $(document).ready(function () {

        /**
         * Slider needs to be inited and then destroyed
         */

        var upsellSliderInit = $("#upsell-slider");
        var upsellSliderInitOverlay = $("#upsell-slider .product-overlay");

        if (upsellSliderInit && upsellSliderInitOverlay ) {

            upsellSliderInit.slickInit({});
            upsellSliderInit.slick('unslick');

            /**
             * Get settings such as in Digidirect_ProductOverlay/templates/overlay.phtml
             * so it can be set in admin
             */

            /**
             * Overlay reInit on You may also Like Slider only
             */

            upsellSliderInitOverlay.productOverlay({
                "size": window.overlayOnSlideSize,
                "path": window.overlayOnSliderPath,
                "mode": window.overlayOnSliderMode,
                "hideForConfigurable": window.overlayOnSliderHideForConfigurable
            });

            /**
             * You may also Like Slider inited
             */

            upsellSliderInit.slickInit({
                infinite: false,
                slidesToShow: 2,
                slidesToScroll: 2,
                mobileFirst: true,
                arrows: false,
                responsive: [
                    {
                        breakpoint: 767,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 3
                        }
                    },
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 4,
                            slidesToScroll: 4,
                            arrows: true
                        }
                    },
                    {
                        breakpoint: 1439,
                        settings: {
                            slidesToShow: 5,
                            slidesToScroll: 5,
                            arrows: true
                        }
                    }
                ]
            })
        }
    });
});