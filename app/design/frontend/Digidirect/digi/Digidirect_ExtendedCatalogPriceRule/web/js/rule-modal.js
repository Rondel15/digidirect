define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.catalogPriceRuleModal', {
        options: {
            ruleId: '',
            modal: {
                type: 'popup',
                title: 'Special Offer',
                modalClass: 'modal-catalogrule',
                innerScroll: true,
                buttons: []
            }
        },

        _create: function () {
            this.createModal();
            this.bind();
        },

        bind: function () {
            var self = this;
            this.element.on('click', function () {
                self.getRuleModal().modal('openModal');
            });
        },

        createModal: function () {
            var rule = $('.' + this.options.modal.modalClass).find('[data-rule]').data('rule');

            if (rule === undefined || !this.getRuleModal().length) {
                this.options.modal.title = this.element.text();
                this.element.closest('.block-extendedrule').find('[data-rule]').modal(this.options.modal);
            }
        },

        getRuleModal: function () {
            return $('.' + this.options.modal.modalClass).find('[data-rule="' + this.options.ruleId +'"]');
        }
    });

    return $.digidirect.catalogPriceRuleModal;
});
