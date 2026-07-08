define([
    'jquery',
    'jquery/ui',
    'mage/validation'
], function ($) {
    'use strict';

    $.widget('digidirect.abstractGiftCard', {
        options: {
            serviceSelector: '#default-giftcard-service-code',
            codeSelector: '#default-giftcard-code',
            pinSelector: '#default-giftcard-pin',
            statusUrl: '',
            statusId: '#default-giftcard-balance-lookup',
            checkStatus: '.giftcard-check',
            messages: '.page.messages .messages'
        },
        _create: function () {
            $(this.options.checkStatus).on('click', $.proxy(function () {
                if (this.element.validation().valid()) {
                    this.sendRequest();
                }
            }, this));
        },
        getData: function () {
            return {
                'giftcard_service[service_code]': $(this.options.serviceSelector).val(),
                'giftcard_service[code]': $(this.options.codeSelector).val(),
                'giftcard_service[pin]': $(this.options.pinSelector).val()
            };
        },
        sendRequest: function () {
            var self = this;
            $.ajax({
                url: this.options.statusUrl,
                type: 'POST',
                cache: false,
                showLoader: true,
                data: self.getData(),
                success: function (response) {
                    self.success(response);
                },
                error: function (xhr, status) {
                    self.error(xhr, status);
                }
            });
        },
        success: function (response) {
            $(this.options.messages).hide();
            $(this.options.statusId).html(response);
        },
        error: function (xhr, status) {
        }
    });
    
    return $.digidirect.abstractGiftCard;
});
