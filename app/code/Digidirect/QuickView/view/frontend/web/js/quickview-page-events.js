define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'mage/dataPost'
], function (
    $,
    mageTemplate
) {
    'use strict';

    $.widget('digidirect.quickViewPageEvents', {
        options: {
            openInNewWindow: true,
            compareLink: 'tocompare',
            eventElements: '[data-post]',
            redirectLinks: '.mailto',
            formTemplate: '<form action="<%- data.action %>" method="post" style="display: none;">' +
                          '<% _.each(data.data, function(value, index) { %>' +
                          '<input name="<%- index %>" value="<%- value %>">' +
                          '<% }) %></form>'
        },

        _create: function () {
            $('body').dataPost('disable');
            this._bind();
        },

        /**
         * Bind events for wishlist, compare button and links
         * @private
         */
        _bind: function () {
            var self = this;
            $(document).on('click', this.options.eventElements, function (e) {
                var elem = $(this),
                    data = elem.data('post');
                elem.hasClass(self.options.compareLink) ? self._sendCompareData(data) : self.sendWishListData(data);
                e.preventDefault();
            });

            $(document).on('click', this.options.redirectLinks, function (e) {
                var url = $(this).attr('href');
                self.options.openInNewWindow ? window.open(url) : window.top.location.href = url;
                e.preventDefault();
            });

            $(document).on('ajaxComplete', function (event, xhr) {
                if ($.localStorage.get('isChangedCompareList') && xhr.responseJSON && 'compare-products' in xhr.responseJSON) {
                    window.globalStore.trigger(window.globalEvents.SECTION_UPDATE, 'compare-products');
                    $.localStorage.remove('isChangedCompareList');
                }
            });
        },

        /**
         * Sends data when adding to the comparison
         * @param {Object} params
         * @private
         */
        _sendCompareData: function (params) {
            $.localStorage.set('isChangedCompareList', true);
        },

        /**
         * Sends data for wish list
         * @param {Object} params
         * @param {Boolean} isTopWindow
         */
        sendWishListData: function (params, isTopWindow) {
            var formKey = $('[name=form_key]').val(),
                targetWindow = this._getWindow(isTopWindow),
                form;

            if (formKey) {
                params.data.form_key = formKey;
            }

            form = mageTemplate(this.options.formTemplate, {
                data: params
            });

            try {
                $(form).appendTo(targetWindow.document.body).submit();
            } catch (e) {
                targetWindow.document.body.innerHTML = form;
                $(targetWindow.document.forms[0]).submit();
            }
        },

        /**
         * Returns the window into which the form is inserted
         * @param {Boolean} isTopWindow
         * @return {Object} window
         * @private
         */
        _getWindow: function (isTopWindow) {
            if (!this.targetWindow || this.targetWindow.closed) {
                this.targetWindow = this.options.openInNewWindow ? window.open('about:blank', 'wishListWindow') : window.top;
            }
            return isTopWindow ? window.top : this.targetWindow;
        }
    });
    return $.digidirect.quickViewPageEvents;
});
