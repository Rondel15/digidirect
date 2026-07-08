define([
    'jquery'
], function ($) {


    //$('.product-info-price .current-price-wrapper').insertBefore($('#main-product-qantas'));
    var cashbackLabel = $('.product-info-main').next('block-extendedrule');
    var productInfoPriceBlock = $('.product-info-price');
    if(cashbackLabel){
        productInfoPriceBlock.find('.price-box.price-final_price').addClass('cashback-active');
        if($('.price-box.price-final_price.cashback-active').find('.old-price-label').length === 0)
        {
            productInfoPriceBlock.find('.price-box.price-final_price').addClass('pad-top');
        }

        if( $('.product-info-price .current-price-wrapper').length )         // use this if you are using class to check
        {
            $('.block-extendedrule').insertAfter( $('.product-info-price'));

        }
        else
        {
            $('.block-extendedrule').insertAfter( $('.product-info-price'));
        }


    }
});
