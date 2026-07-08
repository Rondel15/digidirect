define([
    'jquery',
    'matchMedia',
    'domReady!'
], function ($, mediaCheck) {
    'use strict';

    mediaCheck({
        media: '(min-width: 768px)',
        entry: function () {
            window.onscroll = false;
        },
        exit: function () {
            if (!window.addToCartFlag) {
                setBoxContent();
            }
            window.addToCartFlag = true;
            setBoxContent();

            window.onscroll = function() {showBoxToCart()};
        }
    });

    var sticky = 103;
    var container = document.getElementsByClassName('btn-wrapper-pdp')[0];

    function setBoxContent () {
        var toCartButton = document.getElementById('product-addtocart-button');
        var newButtonPlace = document.getElementById('add-cart-btn');

        var elementBeforeCashBack = document.getElementsByClassName('before-cashback')[0];
        var elementAfterCashBack = document.getElementsByClassName('after-cashback')[0];

        var elementPromotionalPrice = document.getElementsByClassName('special-price')[0];

        var newPlaceBeforeCB = document.getElementById('beforeCB');
        var newPlaceAfterCB = document.getElementById('afterCB');
        var priceFull = document.getElementById('priceFull');
        var newPlacePricePromotional = document.getElementById('pricePromotional');

        if (!$('.product-add-form .before-cashback').length) {
            elementBeforeCashBack = null;
        }
        if (!$('.product-add-form .after-cashback').length) {
            elementAfterCashBack = null;
        }
        if (!$('.product-add-form .special-price').length) {
            elementPromotionalPrice = null;
        }

        if (elementPromotionalPrice) {
            $(newPlaceBeforeCB).css('display', 'none');
            $(newPlaceAfterCB).css('display', 'none');
            priceFull.classList.add('old-price');
        } else if (!elementPromotionalPrice && (elementBeforeCashBack || elementAfterCashBack)) {
            $(priceFull).css('display', 'none');
            $(newPlacePricePromotional).css('display', 'none');
        } else if (elementPromotionalPrice && (elementBeforeCashBack || elementAfterCashBack)) {
            $(newPlaceBeforeCB).css('display', 'none');
            $(newPlaceAfterCB).css('display', 'none');
            priceFull.classList.add('old-price');
        } else {
            $(newPlaceBeforeCB).css('display', 'none');
            $(newPlaceAfterCB).css('display', 'none');
            $(newPlacePricePromotional).css('display', 'none');
        }
        if (elementBeforeCashBack) {
            var clonedBeforeCB = elementBeforeCashBack.cloneNode(true);
            newPlaceBeforeCB.appendChild(clonedBeforeCB);
        }
        if (elementAfterCashBack) {
            var clonedAfterCB = elementAfterCashBack.cloneNode(true);
            newPlaceAfterCB.appendChild(clonedAfterCB);
        }
        if (elementPromotionalPrice) {
            var clonedPricePromotional = elementPromotionalPrice.cloneNode(true);
            newPlacePricePromotional.appendChild(clonedPricePromotional);
            $(newPlaceBeforeCB).css('display', 'none');
            $(newPlaceAfterCB).css('display', 'none');
        }

        var clone = toCartButton.cloneNode(true);
        newButtonPlace.appendChild(clone);
    }

    function showBoxToCart () {
        if (window.pageYOffset > sticky) {
            container.classList.add('-move-top');
        } else {
            container.classList.remove('-move-top');
        }
    }
});
