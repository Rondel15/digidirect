define([
    'jquery',
    'underscore',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/confirm',
    'jquery/jquery-storageapi',
    'jquery/ui',
    'loader'
], function ($, _, $t, alert, confirm) {
    'use strict';

    return function (target) {
        $.widget('digidirect.orderGroupManage', target, {
            options: {
                draggableItem: '[data-role="draggable-item"]',
                deleteItemConfirmationTrigger: '[data-role="delete-item"]',
                configConfirm: {
                    modalClass: "confirm shelf-popup",
                    content: $t('Are you sure you want to ') + '<span style="color:#bc4a4a">delete</span>' +  $t(' your kit?'),
                    buttons: [{
                        text: $t('No, take me back'),
                        class: 'action-secondary',
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }, {
                        text: $t('Yes, I\'m sure'),
                        class: 'action-secondary action-accept',
                        click: function (event) {
                            this.closeModal(event, true);
                        }
                    }]
                },
                configItemConfirm: {
                    modalClass: "confirm shelf-popup del-item",
                    content: $t('This item will be moved to the end of the list. Do you want to continue?')
                }
            },

            _bind: function () {
                this._super();
                $(this.options.deleteItemConfirmationTrigger).on('click', $.proxy(this.deleteItemConfirmation, this));

                $(this.options.draggableItem).draggable({
                    handle: "h2"
                });

            },

            deleteGroupConfirmation: function (e) {
                var self = this;

                e.preventDefault();

                confirm({
                    modalClass: this.options.configConfirm.modalClass,
                    content: this.options.configConfirm.content,
                    buttons: this.options.configConfirm.buttons,
                    actions: {
                        confirm: function (e) {
                            window.location.href = $(self.options.deleteGroupConfirmationTrigger).attr('href');
                        }
                    }
                });
            },

            deleteItemConfirmation: function (e) {
                var self = this;

                e.preventDefault();

                confirm({
                    modalClass: this.options.configItemConfirm.modalClass,
                    content: this.options.configItemConfirm.content,
                    buttons: this.options.configConfirm.buttons,
                    actions: {
                        confirm: function (e) {
                            window.location.href = $(self.options.deleteItemConfirmationTrigger).attr('href');
                        }
                    }
                });
            },

            dropItem: function (e, ui) {
                var item = ui.helper,
                    itemId = item[0].attributes['data-item-id'].value;
                
                if (this.options.allItemsIds.includes(itemId)) {
                    this.showErrorAlert();
                } else if (this.isNewItem(itemId)) {
                    this.addNewItem(itemId);
                } else {
                    this.showErrorAlert();
                }
                this.clearAllModes();
            },

            showErrorAlert: function (err) {
                var content = err || this.options.alertContent + $(this.options.groupName).text();
                alert({
                    modalClass: this.options.configConfirm.modalClass,
                    content: content
                });
            },

        });

        return $.digidirect.orderGroupManage;
    };
});
