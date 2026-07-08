define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.attributeAlphabetical', {
        options: {
            letterItems: $('[data-role="alphabetical-letter"]'),
            letterContainers: $('.alphabetical-section'),
            letterHiddenList: $('.list.-hide'),
            showMoreButton: $('[data-role="alphabetical-more"]'),
            scrollSpeed: 500
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            this.options.letterItems.on('click', $.proxy(function (e) {
                e.preventDefault();
                this._scrollTo($(e.currentTarget).attr('href'));
            }, this));

            this.options.showMoreButton.on('click', $.proxy(function (e) {
                this._showMore($(e.currentTarget));
            }, this));
        },

        _scrollTo: function (sectionId) {
            $('html, body').animate({
                scrollTop: $(sectionId).offset().top
            }, this.options.scrollSpeed, $.proxy(function () {
                this._updateHash(sectionId);
            }, this));
        },

        _updateHash: function (sectionId) {
            window.location.hash = sectionId;
        },

        _showMore: function ($button) {
            $button.closest(this.options.letterContainers).find(this.options.letterHiddenList).attr('aria-hidden', 'false').removeClass('-hide');
            $button.addClass('-hide');
        }
    });

    return $.digidirect.attributeAlphabetical;
});
