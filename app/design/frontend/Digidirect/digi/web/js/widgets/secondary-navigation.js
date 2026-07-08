define([
    'jquery',
    'matchMedia'
], function ($, mediaCheck) {
    'use strict';

    $.widget('digidirect.secondaryNavigation', {
        options: {
            breakpoint: '(min-width: 1280px)',
            element: '.secondary-nav',
            trigger: '.-active',
            navOpenClass: '-open',
            navOpenHtmlClass: 'secondary-nav-open',
            navOverlay: '.secondary-nav-overlay'
        },

        _create: function () {
            mediaCheck({
                media: this.options.breakpoint,
                entry: $.proxy(function () {
                    this._toggleDesktopMode();
                }, this),
                exit: $.proxy(function () {
                    this._toggleMobileMode();
                }, this)
            });
        },

        _toggleDesktopMode: function () {
            $(this.options.element).removeClass(this.options.navOpenClass);
            $('html').removeClass(this.options.navOpenHtmlClass);
            $(this.options.element).off('click', this.options.trigger);
        },

        _toggleMobileMode: function () {
            var self = this,
                $html = $('html'),
                $el = $(self.element),
                $overlay = $(this.options.navOverlay);

            $el.on('click', self.options.trigger, function (e) {
                e.preventDefault();
                $el.toggleClass(self.options.navOpenClass);
                $html.toggleClass(self.options.navOpenHtmlClass);
            });

            $overlay.on('click', function(e) {
                $el.removeClass(self.options.navOpenClass);
                $html.removeClass(self.options.navOpenHtmlClass);
            });
        }
    });

    return $.digidirect.secondaryNavigation;
});
