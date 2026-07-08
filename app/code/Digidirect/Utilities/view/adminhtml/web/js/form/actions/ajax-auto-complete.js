define([
    'underscore',
    'jquery',
    'mage/template',
    'text!Digidirect_Utilities/template/form/element/result-auto-complete.html',
    'text!Digidirect_Utilities/template/modal/modal.html',
    'mage/translate',
    'Magento_Ui/js/modal/modal'
], function (_, $, mageTpl, autoCompTpl, modalTpl) {
    'use strict';

    $.widget('mage.ajaxAutoComplete', {

        options: {
            url: '',
            delay: 1000,
            amountCharacters: 3,
            input: '[data-action="input-text"]',
            btn: '[data-action="done-select"]',
            searchBlock: '[data-role="search-block"]',
            labelBlock: '[data-action="open-search"]',
            selectValue: '[data-action="select"]',
            clearValue: '[data-action="clear"]',
            hiddenInput: '[data-role="hidden-input"]',
            modalTitle: '',
            modalText: '',
            data: {},
            value: '',
            currentVal: '',
            defaultVal: $.mage.__('Select...'),
            additionalData: {},
            translation: {
                done: $.mage.__('Done'),
                noResults: $.mage.__('No results'),
                options: $.mage.__('options')
            },
            entityIdAttribute: 'id',
            entityNameAttribute: 'name'
        },

        /**
         * Build widget
         *
         * @private
         */
        _create: function () {
            this._setElements();
            this._enableClear();
            this.autoCompBlock = mageTpl(autoCompTpl);
            this.modalBlock = mageTpl(modalTpl);
            this._setModal();
            this._bind();
        },

        /**
         * Bind events
         *
         * @private
         */
        _bind: function () {
            var handlers = {};

            handlers['click ' + this.options.labelBlock] = '_showSearch';
            handlers['keyup ' + this.options.input] = '_proceedRequest';
            handlers['click ' + this.options.selectValue] = '_selectValue';
            handlers['click ' + this.options.clearValue] = '_clearValue';
            handlers['click ' + this.options.btn] = '_checkCurrentValue';
            // handlers.click = '_catchEvent';
            $(document).on('click', $.proxy(function (e) {
                var container = this.element;
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    this._hideSearch();
                }
            }, this));
            this._on(handlers);
        },

        /**
         * Set notification modal.
         *
         * @private
         */
        _setModal: function () {
            var self = this,
                popupOptions = {
                    'type': 'popup',
                    'modalClass': 'popup-tree-decline',
                    'responsive': true,
                    'innerScroll': true,
                    'title': this.options.modalTitle,
                    'buttons': [{
                        class: 'action-primary cancel action-accept',
                        type: 'button',
                        text: 'Cancel',

                        /**
                         * Click action.
                         */
                        click: function () {
                            self._hideSearch();
                            this.closeModal();
                        }
                    }, {
                        class: 'action-primary confirm action-accept',
                        type: 'button',
                        text: 'Confirm',

                        /**
                         * Click action.
                         */
                        click: function () {
                            self._setValue();
                            this.closeModal();
                        }
                    }]
                };

            this.modalBlockWrapper = $(this.modalBlock({
                data: this.options.modalText
            }));
            this.modalBlock = $(this.modalBlockWrapper[this.modalBlockWrapper.length - 1]);
            this.modalBlock.modal(popupOptions);
        },

        /**
         * Show search section.
         *
         * @private
         */
        _showSearch: function () {
            this.blocks.searchBlock.slideToggle(0);
            this.blocks.labelBlock.addClass('_active');
            this.setSearchPosition();
        },

        /**
         * Hide search section.
         *
         * @private
         */
        _hideSearch: function () {
            this._removeResult();
            this.blocks.searchBlock.hide();
            this.blocks.labelBlock.removeClass('_active');
            this.blocks.inputBlock.val('');
            this.blocks.searchBlock.removeClass('-top');
        },

        setSearchPosition: function () {
            var searchBlockHeight = parseInt(this.blocks.searchBlock.css('height'), 10),
                labelBlockTopOffset = this.blocks.labelBlock.offset().top;

            if (parseInt($('body').css('height'), 10) > (searchBlockHeight + labelBlockTopOffset) + parseInt(this.blocks.labelBlock.css('height'), 10)) {
                this.blocks.searchBlock.removeClass('-top');
            } else {
                if (searchBlockHeight > labelBlockTopOffset) {
                    this.blocks.searchBlock.find('.admin__action-multiselect-menu-inner').css('maxHeight', (searchBlockHeight - 150) + 'px');
                }
                this.blocks.searchBlock.addClass('-top');
            }
        },

        /**
         * Catch event without this.
         *
         * @private
         */
        _catchEvent: function (e) {
            e.stopPropagation();
        },

        /**
         * Mark select element.
         *
         * @private
         */
        _selectValue: function (e) {
            var listEl = $(e.target);

            this.blocks.list.removeClass('_selected');
            listEl.addClass('_selected');
            this.options.value = listEl.data('name');
            this.options.id = listEl.data('id');
            $(this.options.btn).prop({
                disabled: false
            });
        },

        /**
         * Clear selected value.
         *
         * @private
         */
        _clearValue: function () {
            this.blocks.labelBlock.text(this.options.defaultVal);
            this.blocks.hiddenBlock.val('');
            this.blocks.hiddenBlock.trigger('change');
            this.blocks.clearValue.addClass('no-display');
        },

        /**
         * Check to set or change value.
         *
         * @private
         */
        _checkCurrentValue: function () {
            if (this.options.currentVal && this.blocks.hiddenBlock.val() && this.options.id !== +this.blocks.hiddenBlock.val()) {
                this.modalBlock.modal('openModal');
            } else {
                this._setValue();
            }
        },

        /**
         * Set received value from server.
         *
         * @private
         */
        _setValue: function () {
            this.blocks.labelBlock.text(this.options.value);
            this.blocks.hiddenBlock.val(this.options.id);
            this.blocks.hiddenBlock.trigger('change');
            this.blocks.clearValue.removeClass('no-display');
            this._hideSearch();
        },

        /**
         * Render result auto completer.
         *
         * @private
         */
        _renderResult: function () {
            this._removeResult();
            this.resultBlock = $(this.autoCompBlock({
                data: $.extend(this.options.data, this.options.translation)
            }));

            this.blocks.searchBlock.append(this.resultBlock[this.resultBlock.length - 1]);
            this.blocks.list = this.element.find(this.options.selectValue);

            this.setSearchPosition();
        },

        /**
         * Remove result auto completer.
         *
         * @private
         */
        _removeResult: function () {
            if (this.resultBlock) {
                this.resultBlock.remove();
            }
        },

        /**
         * Set DOM elements.
         *
         * @private
         */
        _setElements: function () {
            this.blocks = {};
            this.blocks.inputBlock = this.element.find(this.options.input);
            this.blocks.labelBlock = this.element.find(this.options.labelBlock);
            this.blocks.searchBlock = this.element.find(this.options.searchBlock);
            this.blocks.clearValue = this.element.find(this.options.clearValue);
            this.blocks.hiddenBlock = this.element.find(this.options.hiddenInput);
        },

        _enableClear: function () {
            if (this.blocks.clearValue && this.blocks.clearValue.length) {
                if (this.options.currentVal) {
                    this.blocks.clearValue.removeClass('no-display');
                }
            }
        },

        /**
         * Check amount characters
         *
         * @private
         */
        _proceedRequest: function (e) {
            this._clearDelay();

            if (e.target.value.length >= this.options.amountCharacters) {
                this._delayRequest();
            }
        },

        /**
         * Clear delay for ajax.
         *
         * @private
         */
        _clearDelay: function () {
            clearTimeout(this.delay);
        },

        /**
         * Set delay for ajax.
         *
         * @private
         */
        _delayRequest: function () {
            this.delay = setTimeout(
                this._sendAjax.bind(this),
                this.options.delay
            );
        },

        /**
         * Send ajax.
         *
         * @private
         */
        _sendAjax: function () {
            var sendData = {
                name: this.blocks.inputBlock.val(),
                additionalData: this.options.additionalData
            };

            $.ajax({
                url: this.options.url,
                data: sendData,
                type: 'post',
                dataType: 'json',
                showLoader: true,
                contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                processData: true,

                /**
                 * @callback
                 */
                success: $.proxy(function (items) {
                    this.options.data.list = this.prepareItems(items);
                    this._renderResult();
                }, this)
            });
        },

        /**
         * Prepare items for auto complete.
         *
         * @param {Array} items
         * @returns {Array}
         */
        prepareItems: function (items) {
            var self = this;

            return _.each(
                items,
                function (item) {
                    item.id = item[self.options.entityIdAttribute];
                    item.name = item[self.options.entityNameAttribute];
                },
                this
            );
        }
    });

    return $.mage.ajaxAutoComplete;
});
