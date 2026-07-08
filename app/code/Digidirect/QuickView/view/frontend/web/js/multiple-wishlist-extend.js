define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'quickViewPageEvents',
    'jquery/ui'
], function ($, alert) {
    'use strict';
    return function () {
        $.widget('mage.multipleWishlist', $.mage.multipleWishlist, {
            options: {
                quickViewInitSelector: '#maincontent'
            },
            _create: function () {
                this._super();
                this.quickViewMode = window.top !== window;
            },

            _addToNew: function (data) {
                if (this.quickViewMode) {
                    this._callback = $.proxy(function (wishlistId) {
                        data.data.wishlist_id = wishlistId;
                        $(this.options.quickViewInitSelector).quickViewPageEvents('sendWishListData', data, true);
                    }, this);
                    this._showCreateWishlist(this.options.createUrl, true);
                } else {
                    return this._super(data);
                }
            },

            _createWishlistAjax: function (form) {
                if (this.quickViewMode) {
                    var _form = $(form), _this = this;
                    $.ajax({
                        url: _form.attr('action'),
                        type: 'post',
                        cache: false,
                        data: _form.serialize(),
                        success: function (response) {
                            $('#' + _this.options.popupWishlistBlockId).modal('closeModal');
                            if (typeof response['wishlist_id'] !== 'undefined') {
                                if (_this._callback) {
                                    _this._callback(response.wishlist_id);
                                }
                            } else if (typeof response['redirect'] !== 'undefined') {
                                _this._redirectWindow(response['redirect']);
                            } else {
                                alert({
                                    content: _this.options.errorMsg
                                });
                            }
                            _this.createTmpl.hide();
                        }
                    });
                } else {
                    return this._super(form);
                }
            },

            _redirectWindow: function (url) {
                window.top.location.href = url;
            }
        });
        return $.mage.multipleWishlist;
    };
});
