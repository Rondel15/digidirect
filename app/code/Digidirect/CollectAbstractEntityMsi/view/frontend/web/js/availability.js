define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.collectPlacesAvailability', {
        options: {
            url: '',
            form: '#product_addtocart_form',
            qty: '#qty, .table-wrapper.grouped .input-text.qty',
            event: 'change'
        },

        _create: function () {
            this._bind();
        },

        _bind: function () {
            $(this.options.qty).on(this.options.event, this.sendRequest.bind(this));
            this.element.on('updateSwatches', this.sendRequest.bind(this));
        },

        sendRequest: function () {
            if ($(this.options.qty).val() === '') {
                return;
            }
            var $widget = this,
                formData = new FormData($($widget.options.form)[0]);

            formData.set('product_id', formData.get('product'));

            $widget._XhrKiller();

            $widget.xhr = $.ajax({
                url: this.options.url,
                data: formData,
                type: 'POST',
                dataType: 'json',
                contentType: false,
                processData: false
            });

            $widget.onSendRequest($widget.xhr);

            return $widget.xhr;
        },

        onSendRequest: function (xhr) {
            var self = this;
            xhr.done(function (response) {
                self.onSuccessRequest(response);
            });
        },

        onSuccessRequest: function (response) {
            this.element.html(response.content);
            this._XhrKiller();
        },

        _XhrKiller: function () {
            var $widget = this;

            if ($widget.xhr !== undefined && $widget.xhr !== null) {
                $widget.xhr.abort();
                $widget.xhr = null;
            }
        }
    });

    return $.digidirect.collectPlacesAvailability;
});
