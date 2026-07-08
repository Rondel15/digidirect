define([
    'jquery',
    'jquery/ui',
    'domReady!'
], function ($) {
    $.widget('digidirect.scrollShelf', {
        _create: function () {
            this._bind();
        },

        _bind: function () {
            var scrollshelf = window.localStorage.getItem('scrollshelf');
            if (scrollshelf) {
                window.scrollTo(0, scrollshelf);
                $(window).on('scroll', function () {
                    window.localStorage.setItem('scrollshelf', $(window).scrollTop());
                });
            } else {
                $(window).on('scroll', function () {
                    window.localStorage.setItem('scrollshelf', $(window).scrollTop());
                });
            }
        }
    });
    return $.digidirect.scrollShelf;
});