//redeploy
//clint
define([
    'jquery'
], function ($) {
    //clint changes cashback
    //Redeploy
    
    $('.secure-pay-container').ready(function() {
        $(".secure-pay-container").attr("style", "display: none");
    });

    $('.fotorama__wrap').ready(function() {
        console.log("Fotorma has been loaded!");
        setTimeout(function() {
        $('.custom-preloader').attr("style", "display:none !important;");
        $('.digiseconds-overlay').attr("style", "display:block;");
        }, 1000);
    });

    $(window).on('load', function(){
        if ($(window).width() <= 768) {
            console.log("Test OnLoad : Window width is " + $(window).width());
        }
        $('#product-addtocart-button').removeAttr("title");
    });
    
    var win = $(this); //this = window
    if (win.width() >= 768) {
        $('.page-title-wrapper').insertBefore($('.mrkt-product-info-seller'));
    } else {
        $('.page-title-wrapper').insertBefore($('.media-area'));
    }

    //for mobile = 760
    //Changed to 1439 for tablet *Rondel
    //let isMobile = window.matchMedia("only screen and (max-width: 1439px)").matches;

    $(window).on('resize', function(){
        if (win.width() >= 768) {
            $('.page-title-wrapper').insertBefore($('.mrkt-product-info-seller'));
        } else {
            $('.page-title-wrapper').insertBefore($('.media-area'));
        }
    });
    
    if($('#product-options-wrapper .control').length) {
        $('#product-options-wrapper').attr("style", "display:block !important;");
    }

    $('#itoris-pm-link-custom').on('click', function(){
        $('#itoris-pm-link').trigger("click");
        console.log("Changed To Trigger Click!");
    });
    
    $(document).ready(function(){
        $('#zip-custom').on('click', function(){
            $('#zip-product-widget').trigger("click");
            console.log("ZIP Custom Clicked!");
        });
    });      
    
    $('#payment-options-toggle').on('click', function(){
        if($(this).hasClass("close")) {
            $(this).removeClass("close");
            $(this).addClass("open");
            $('#payment-options').removeClass("close");
            $('#payment-options').addClass("open");
            console.log("Remove Close");
        } else if($(this).hasClass("open")) {
            $(this).removeClass("open");
            $(this).addClass("close");
            $('#payment-options').removeClass("open");
            $('#payment-options').addClass("close");
            console.log("Remove Open");
        }
    });
    
});
