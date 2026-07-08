require([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'ko',
    'Magento_Checkout/js/model/totals',
    'Magento_Catalog/js/price-utils',
    'domReady!'
], function ($, quote, ko, totals, utils) {
    'use strict';

    function showBarOnCheckout () {

        var actionLeftBlock = document.getElementById('leftBlock');
        var checkoutCartButton = document.getElementById('checkoutOffCanvas');
        var checkoutCartButtonItems = document.getElementById('checkoutOffCanvasItems');
        var checkoutCartContentBar = document.getElementById('checkoutOffCanvasBar');
        var barClose = document.getElementById('barClose');

        var priceFormat = window.checkoutConfig.priceFormat;
        var valuePlace = $('.checkout-menu > .order-sum > .value');
        var value = quote.totals()['grand_total'];
        var valueItems = quote.totals()['items'].length;
        var priceFormated = utils.formatPrice(value, priceFormat);

        // quote and items in cart change START

        valuePlace[0].innerText = priceFormated;
        checkoutCartButtonItems.innerText = valueItems;

        quote.totals.subscribe(function () {
            value = quote.totals()['grand_total'];
            priceFormated = utils.formatPrice(value, priceFormat);
            valuePlace[0].innerText = priceFormated;
            valueItems = quote.totals()['items'].length;
            checkoutCartButtonItems.innerText = valueItems;
        }, null, 'change');

        // END

        // open bar START

        function openPanel() {
            if (checkoutCartContentBar.classList.contains('-bar-open') && actionLeftBlock.classList.contains('-bar-open')) {
                checkoutCartContentBar.classList.remove('-bar-open');
                actionLeftBlock.classList.remove('-bar-open');
            } else {
                checkoutCartContentBar.classList.add('-bar-open');
                actionLeftBlock.classList.add('-bar-open');
            }
        }

        checkoutCartButton.addEventListener('click', function () {
            openPanel();
        });

        barClose.addEventListener('click', function () {
            openPanel();
        });

        actionLeftBlock.addEventListener('click', function () {
            openPanel();
        });


        var sticky = 76;
        var container = document.getElementsByClassName('checkout-menu')[0];

        window.onscroll = function () {
            showBoxToCart()
        };

        function showBoxToCart() {
            if (window.pageYOffset > sticky) {
                container.classList.add('-move-top');
            } else {
                container.classList.remove('-move-top');
            }
        }

        // open bar End
    }

    showBarOnCheckout ();
});