define(['jquery'], function($){
    "use strict";

    return function anchortag()
    {

        $(window).on('load', function (e) {

            $('#storelocatoranchor').ready(function() {

                var hash = window.location.hash.substr(1);
                if(hash === '')
                {
                    hash = 'Bondi Junction';
                }

                $(".scontent-"+hash).ready(function() {
                    $('#'+hash).attr('checked', true); //can be removed if it scrolls up the page
                    //$(".scontent-"+hash).css({ display: "block" });
                });
                
                $('.parent-storelist').each(function() {
                    if ($(this).find('._hide-for-mobile').length) {
                        $(this).attr("style", "display:block;");
                    } else {
                        $(this).attr("style", "display:none !important;");
                    }
                });

            });
        })



    }
});
