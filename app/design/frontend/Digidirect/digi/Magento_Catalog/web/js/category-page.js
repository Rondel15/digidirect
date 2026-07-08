//rondel
//Redeploy
define([
    'jquery'
], function ($) {
    
    //$('.columns').append('<div class="custom-preloader-container""><div class="custom-preloader"></div></div>');
    //window.onload = function() {   
        
    //};
    
    $('.products-grid').ready(function() {
        $('.desktop-row').attr("style", "visibility: visible !important;");
        setTimeout(function() {
            $('.block-content.filter-content').attr("style", "display: block !important;").fadeIn();
        }, 3000);
    });

});
