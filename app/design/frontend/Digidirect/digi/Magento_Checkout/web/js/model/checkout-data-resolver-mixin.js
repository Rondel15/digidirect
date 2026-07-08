define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-shipping-method',
    'Magento_Customer/js/customer-data'
], function (
    $,
    quote,
    checkoutData,
    selectShippingMethodAction,
    customerData
) {
    'use strict';
    return function (target) {
        target.resolveShippingRates = function (ratesData) {
            var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                availableRate = false,
                freeshippingExist = ratesData.filter(function(item) {
                    return item.method_code == 'freeshipping';
                }),
                flaterateExist = ratesData.filter(function(item) {
                    return item.method_code == 'flatrate';
                }),
                shippingExist = ratesData.filter(function (item) {
                    return item.method_code == 'shipping';
                });

            if (ratesData.length === 1) {
                //set shipping rate if we have only one available shipping rate
                selectShippingMethodAction(ratesData[0]);

                return;
            }

            if (quote.shippingMethod()) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] == quote.shippingMethod()['carrier_code'] && //eslint-disable-line
                        rate['method_code'] == quote.shippingMethod()['method_code']; //eslint-disable-line eqeqeq
                });
            }

            if (!availableRate && selectedShippingRate) {
                availableRate = _.find(ratesData, function (rate) {
                    return rate['carrier_code'] + '_' + rate['method_code'] === selectedShippingRate;
                });
            }

            if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                availableRate = window.checkoutConfig.selectedShippingMethod;
                selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);

                return;
            }

            //Unset selected shipping method if not available
            if (!availableRate) {
                selectShippingMethodAction(null);
            } else {
                selectShippingMethodAction(availableRate);
            }

//            if (shippingExist.length > 0 && !availableRate) {
//                var shippingAmount = shippingExist[0].base_amount;
//
//                if (shippingAmount === 0) {
//                    selectShippingMethodAction(shippingExist[0]);
//
//                    var msg = '<svg xmlns="http://www.w3.org/2000/svg" class="free-message svg-icon -free"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#svgi-shipping"></use></svg>' + 'You have qualified for free shipping!';
//
//                    customerData.set('messages', {
//                        messages: [{
//                            type: 'success',
//                            text: msg
//                        }]
//                    });
//
//                    var element = $('.free-message');
//                    element.parent().prop('id','removedBefore');
//
//                    return
//                }
//            }

            if (freeshippingExist.length > 0 && !availableRate) {
                selectShippingMethodAction(freeshippingExist[0]);
            } else if(flaterateExist.length > 0 &&!availableRate) {
                selectShippingMethodAction(flaterateExist[0]);
            }

            function checkoutMobileCart () {

                var checkoutOffCanvasBar = document.getElementById('checkoutOffCanvasBarContent'),
                checkoutOffCanvasSummary = document.getElementById('mobileCheckout'),
                checkoutOffCanvasItems = $('#mobileCheckout .items-in-cart'),
                checkoutOffCanvasButton = $('#mobileCheckout .single-actions .button');

                // Move items copy to Right Bar on Checkout page

                $(checkoutOffCanvasItems).appendTo(checkoutOffCanvasBar);

                // Move summary copy to Right Bar on Checkout page

                $(checkoutOffCanvasSummary).appendTo(checkoutOffCanvasBar);

                var btn = $('#checkoutOffCanvasBarContent .button.action');
                btn.on('click',function () {
                    var checkoutCartContentBar = document.getElementById('checkoutOffCanvasBar');
                    var actionLeftBlock = document.getElementById('leftBlock');

                    checkoutCartContentBar.classList.remove('-bar-open');
                    actionLeftBlock.classList.remove('-bar-open');
                })

            }

            checkoutMobileCart()
        };

        return target;
    };
});
