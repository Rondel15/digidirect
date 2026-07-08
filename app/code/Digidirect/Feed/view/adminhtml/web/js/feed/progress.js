define([
    'jquery',
    'jquery/ui',
    'uiComponent',
    'ko',
    'underscore'
], function ($, ui, Component, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Digidirect_Feed/feed/progress'
        },

        listener: false,

        initialize: function () {
            var self = this;

            this._super();

            _.bindAll(this, 'afterRender');

            this.state = ko.observable({});
        },

        afterRender: function (element) {
            var self = this;

            this.$element = $(element);

            this.$element.dialog({
                modal: true,
                autoOpen: false,
                resizable: false,
                title: $('h1.page-title').html(),

                open: function () {
                    $(this).closest('.ui-dialog').addClass('ui-dialog-active').addClass('feed__dialog-progress');
                },

                close: function () {
                    self.mute();
                    $(this).closest('.ui-dialog').removeClass('ui-dialog-active');
                }
            });
        },

        show: function () {
            if (!this.$element.dialog('isOpen')) {
                this.$element.dialog('open');
            }
        },

        hide: function () {
            if (this.$element.dialog('isOpen')) {
                this.$element.dialog('close');
            }
        },

        observeExport: function () {
            this.listener = true;
            this.listen();
        },

        mute: function () {
            var self = this;

            this.listener = false;
            if (this.request) {
                setTimeout(function () {
                    self.request.abort();
                    self.request = null;
                }, 500);
            }
        },

        setProgress: function (progress) {
            this.state(progress);
        },

        listen: function () {
            var self = this;

            if (!self.listener) {
                return;
            }

            self.request = $.ajax(self.url, {
                method: 'GET',
                data: {
                    id: self.id,
                    rand: Math.random()
                },

                complete: function (response) {
                    if (self.listener) {
                        if (response.status == 200) {
                            if ($.parseJSON(response.responseText)) {
                                self.state($.parseJSON(response.responseText));
                            }
                        }

                        setTimeout(function () {
                            self.listen();
                        }, 200);
                    }
                },

                error: function (xhr) {
                    if (xhr.status === 0) {
                        console.warn('Request has been canceled because the feed was generated.');
                    }
                }
            });
        }
    });
});
