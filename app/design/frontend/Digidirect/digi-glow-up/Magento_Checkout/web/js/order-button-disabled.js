define([
    'jquery'
], function ($) {
    'use strict';

    $(window).click(function () {
        if ($('#payment-method-braintree-paypal').hasClass('_active')) {
            $('.single-actions').css('display', 'none');
            $("#payment-button-extension").css("display", "none");
        } else {
            $('.single-actions').css('display', 'block');
            $("#payment-button-extension").css("display", "block");
        }
    });

    $('.opc-progress-bar-item span').click(function () {
        
        $("#co-payment-form").trigger("reset");
        $("#co-transparent-form-braintree").trigger("reset");
        $(".payment-method-braintree").removeClass('_active');
        $(".payment-method").removeClass('_active');
        
        if ($('#braintree_paypal').is(':checked')) {
            $('#payment-method-braintree-paypal').removeClass('_active');
        }
        $('.single-actions').css('display', 'block');
        $("#payment-button-extension").css("display", "block");
    })
});
