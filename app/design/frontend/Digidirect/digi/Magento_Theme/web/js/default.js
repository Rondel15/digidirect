require(['jquery', 'owlcarousel'],
    function ($) {
            alert("test");
        $(document).ready(function() {
            $('.product-carousel').owlCarousel({
                loop:false,
                nav:false,
                items:1,
                dots:true,
                autoplay:true,
                autoplayTimeout:3000
            })
        });
    }
);