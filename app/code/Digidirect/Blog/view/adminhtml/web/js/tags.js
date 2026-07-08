define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.blogTags', {
        options: {
            tagList: $('.tag-list .admin__control-text'),
            tagItems: $('.post-tag')
        },
        _create: function () {
            this.closeElement = this.element.find('#close-tags');
            this._bind();
        },
        _bind: function () {
            this.options.tagList.on('focus', $.proxy(function () {
                this.element.show();
            }, this));
            this.closeElement.on('click', $.proxy(function () {
                this.element.hide();
            }, this));
            this.options.tagItems.on('click', $.proxy(function (e) {
                var tagText = $.trim($(e.currentTarget).html()),
                    tagFieldValue = this.options.tagList.val();
                if (tagFieldValue !== '') {
                    this.options.tagList.val(tagFieldValue + ',' + tagText);
                } else {
                    this.options.tagList.val(tagText);
                }
                this.options.tagList.trigger('change');
            }, this));
        }
    });
    return $.digidirect.blogTags;
});
