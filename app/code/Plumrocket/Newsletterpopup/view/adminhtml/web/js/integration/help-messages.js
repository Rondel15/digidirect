/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

define([
    'jquery',
    'underscore',
    'mage/translate',
    'domReady!'
], function ($, _) {
    'use strict';

    var config = {
        constantcontact: {
            message: {
                text: 'Click "Save Config" to proceed with setup of Constant Contact',
                /** notice|success| */
                style: 'notice',
            },
            on: {
                '#prnewsletterpopup_integration_constantcontact_key': {
                    change: function (elem) {
                        return elem.value !== '' && elem.value !== '******';
                    },
                    paste: function (elem) {
                        setTimeout(function () {
                            $(elem).trigger('change');
                        });
                    },
                },
                '#prnewsletterpopup_integration_constantcontact_secret': {
                    change: function (elem) {
                        return elem.value !== '' && elem.value !== '******';
                    },
                    paste: function (elem) {
                        setTimeout(function () {
                            $(elem).trigger('change');
                        });
                    },
                },
            },
            /** every|some */
            require: 'every',
            position: {
                type: 'after',
                selector: '#row_prnewsletterpopup_integration_constantcontact_secret .value',
            },
        },
    };

    /**
     * @param {{}} config
     * @constructor
     */
    function IntegrationMessagesClass(config)
    {
        /**
         * Current class instance
         *
         * @type {{
         *   config: {},
         *   init: function,
         *   initHandlers: function,
         *   renderMessage: function,
         *   decorateMessage: function,
         * }}
         */
        var self = this;

        /**
         * @type {{}}
         */
        self.config = config;

        self.init = function (intergration) {
            if (self.config[intergration]) {
                var config = self.config[intergration];

                var callback;

                if (config.message) {
                    callback = function (canShowParam) {
                        if (config.require === 'every' && _.every(canShowParam)
                            || config.require === 'some' && _.some(canShowParam)
                        ) {
                            self.renderMessage(config.message, config.position);
                            return true;
                        }

                        return false;
                    };
                }

                if (config.on) {
                    self.initHandlers(config.on, callback);
                }
            }
        };

        /**
         * @param {{}} handlerConfig
         * @param {function|undefined} callback
         */
        self.initHandlers = function (handlerConfig, callback) {
            var canShow = {};

            _.each(handlerConfig, function (config, selector) {
                var elem = $(selector);
                canShow[selector] = 0;
                if (! elem.length) {
                    console.error('HTML Element with selector "' + selector + '" not found');
                    return true;
                }

                _.each(config, function (isValid, eventType) {
                    $(selector).on(eventType, function (event) {
                        if (false === canShow) {
                            return;
                        }

                        canShow[selector] = +isValid(event.target);
                        if (callback(canShow)) {
                            canShow = false;
                        }
                    });
                });
            });
        };

        self.renderMessage = function (message, position) {
            var messageElem = self.decorateMessage(message);
            if (position.type === 'after') {
                $(position.selector).append(messageElem);
            }
        };

        /**
         * Retrieve prepared Object for success results
         *
         * @return {HTMLElement}
         */
        self.decorateMessage = function (message) {
            var messageElement = document.createElement('DIV');
            messageElement.className = 'message message-' + message.style + ' ' + message.style;

            messageElement.style.background = 'none';
            switch (message.style) {
                case 'notice':
                    break;
                case 'success':
                    messageElement.style.color = 'green';
                    break;
                case 'error':
                    messageElement.style.color = 'red';
                    break;
            }

            var messageWrapper = document.createElement('B');
            messageWrapper.innerText = $.mage.__(message.text);

            messageElement.append(messageWrapper);

            return messageElement;
        };
    }

    return new IntegrationMessagesClass(config);
});
