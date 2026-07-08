require([
    'jquery',
    'Digidirect_FreeGift/js/popup',
    'jquery/jquery.cookie'
], function ($) {
    'use strict';

    var popupContainer = $('.freegift-items-add'),
        overlay = $('[data-role=freegift-overlay]'),
        openButton = $('[data-role=freegift-popup-show]'),
        displayOnce = popupContainer.data('display-once'),
        autoOpen = popupContainer.data('auto-open') || window.location.hash == '#choose-gift',
        cookieKey = 'freegift_popup_shown';

    if (autoOpen && displayOnce) {
        if (!$.cookie(cookieKey)) {
            $.cookie(cookieKey, true);
        } else {
            autoOpen = false;
        }
    }

    overlay.freegiftPopup({
        autoOpen: autoOpen,
        slickSettings: {
            slidesToShow: 1,
            slidesToScroll: 1,
            dots: false,
            swipe: false,
            useTransform: false,
            adaptiveHeight: true,
            arrows: false,
            infinite: false,
            fade: true,
            respondTo: 'slider'
        }
    });

    $('.cart-summary').before(popupContainer);
    popupContainer.removeClass('no-display');
    openButton.click(function () {
        overlay.freegiftPopup('show');
        return false;
    });
});
