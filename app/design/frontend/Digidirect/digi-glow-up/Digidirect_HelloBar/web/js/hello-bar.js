require(['jquery'], function($) {
    $(document).ready(function() {
        var slides = $('.hellobar-slide');
        var current = 0;

        function showSlide(index) {
            slides.removeClass('active').eq(index).addClass('active');
            $('.hellobar-dot').removeClass('active').eq(index).addClass('active');
        }

        function nextSlide() {
            current = (current + 1) % slides.length;
            showSlide(current);
        }

        setTimeout(() => {
            showSlide(current);
            setInterval(nextSlide, 5000);
        }, 200);

        var dotsHtml = '';
        slides.each(function (index) {
            dotsHtml += `<span class="hellobar-dot" data-index="${index}"></span>`;
        });
        $('.hellobar-slider').append(`<div class="hellobar-dots">${dotsHtml}</div>`);

        $('.hellobar-slider').on('click', '.hellobar-dot', function() {
            current = parseInt($(this).data('index'));
            showSlide(current);
        });

    });
});
