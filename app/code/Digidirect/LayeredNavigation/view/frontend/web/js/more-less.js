define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.layeredNavigationMoreLess', {
        options: {
            enableMoreLess: true,
            filterContent: '.filter-options-content',
            moreLessLists: '[data-role="more-less-block"]'
        },
        _create: function () {
            this.bind(this.options);
        },
        bind: function (options) {
            var self = this;
            this.element.on('click', function (e) {
                var $this = $(e.currentTarget);
                $this.closest(options.filterContent).find(options.moreLessLists).toggleClass('-hide');
                $this.toggleClass('-active');
                self.updateText($this);
            });
        },
        /**
         * Update text and title
         * @param element
         */
        updateText: function (element) {
            var moreText = element.data('text-more'),
                lessText = element.data('text-less');
            if (!element.hasClass('-active')) {
                element.attr('title', moreText);
                element.text(moreText);
            } else {
                element.attr('title', lessText);
                element.text(lessText);
            }
        }
    });

    return $.digidirect.layeredNavigationMoreLess;
});
