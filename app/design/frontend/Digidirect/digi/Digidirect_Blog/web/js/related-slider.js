define([
    'jquery',
    'matchMedia',
    'domReady!',
    'jquery/ui',
    'slickInit',
    'redirectUrl'
], function ($) {
    'use strict';

    mediaCheck({
        media: '(min-width: 767px)',
        entry: function () {
            $("#related-slider").slickInit({
                infinite: false,
                draggable: true,
                swipe: true,
                dots: false,
                slidesToShow: 5,
                slidesToScroll: 1
            })
        },
        exit: function () {
            $("#related-slider").slickInit({
                infinite: false,
                draggable: true,
                swipe: true,
                dots: false,
                arrows: true,
                slidesToShow: 2,
                slidesToScroll: 1
            })
        }
    });
});