/**
 * @package     Plumrocket_Newsletterpopup
 * @copyright   Copyright (c) 2018 Plumrocket Inc. (https://plumrocket.com)
 * @license     https://plumrocket.com/license   End-user License Agreement
 */

require([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'domReady!'
], function ($, __, alert) {
    'use strict';

    $('button.integration-test-connection').each(function () {
        var labelEl = $(this).parents('tr:first').find('label');
        labelEl.attr('for', labelEl.attr('for') + '_fix');

        setTimeout(function () {
            $('.newsletterpopup_list_readonly').each(function () {
                var elInherit = $('#' + this.id + '_inherit');

                if (elInherit.length) {
                    elInherit.attr('disabled', true);
                    elInherit.attr('onclick', function () { return false; });
                }
            }).attr('disabled', 'disabled');
        }, 500);
    });

    function APIConnectorClass()
    {
        /**
         * Current class instance
         */
        var self = this;

        /**
         * Service code
         *
         * @type {string}
         */
        self.serviceId = '';

        /**
         * Url for account info loading
         *
         * @type {boolean|string}
         */
        self.infoUrl = false;

        /**
         * Url for account info loading
         *
         * @type {boolean|string}
         */
        self.listsUrl = false;

        /**
         * String prefix for result container ids
         *
         * @type {string}
         */
        self.resultContainerPrefix = 'result_container_';

        /**
         * Load account information
         *
         * @return {boolean}
         */
        self.testConnection = function () {
            var resultContainerEl = self.getResultContainerEl(),
                testButton = self.getTestButtonEl();

            /* Validate Service ID */
            if (! self.isValidServiceId()) {
                self.showErrorMessage(__('Service ID not specified.'));

                return false;
            }

            /* Validate info URL */
            if (! self.isValidInfoUrl()) {
                self.showErrorMessage(__('Invalid request destination URL.'));

                return false;
            }

            /* Validate form elements */
            if (! self.isValidFormElements()) {
                testButton.attr('disabled', false);

                return false;
            }

            if (resultContainerEl.length) {
                resultContainerEl.remove();
            }

            testButton.attr('disabled', true);

            $.ajax({
                url: self.infoUrl,
                data: self.getRequestParams(),
                method: 'POST',
                showLoader: true,
                dataType: 'json',
                complete: function (jqXHR, textStatus) {
                    testButton.attr('disabled', false);
                },
                success: function (response, textStatus, jqXHR) {
                    if ('404' === String(response.status)) {
                        self.showErrorMessage(__('Service "%1" not found!', self.serviceId));

                        return false;
                    }

                    var resultParams = self.getResultContainerParams();

                    if (response.result === 'success') {
                        resultParams = self.getResultContainerSuccessParams();

                        if (self.listsUrl) {
                            var originalText = __('Test Connection'),
                                newText = __('Contact Lists Checking...');
                            setTimeout(function () {
                                testButton.attr('disabled', true);
                                testButton.html(testButton.html().replace(originalText, newText));
                            }, 200);

                            setTimeout(function () {
                                testButton.attr('disabled', false);
                                testButton.html(testButton.html().replace(newText, originalText));
                                self.loadLists();
                            }, 2500);
                        }
                    } else {
                        resultParams = self.getResultContainerErrorParams();
                        resultParams.text += '<p>' + response.message + '</p>';
                    }

                    var resultHtml = '<div id="' + self.getResultContainerId() + '" class="' + resultParams.class
                        + '" style="' + resultParams.style + '">' + resultParams.text + '</div>';

                    testButton.after(resultHtml);
                },
                error: function (response) {
                    self.showErrorMessage(__('Something went wrong.') + '' + response.status + ':' + response.statusText);
                }
            });
        };

        /**
         * Load all contact lists
         *
         * @return {boolean}
         */
        self.loadLists = function () {
            /* Validate lists URL */
            if (! self.isValidListsUrl()) {
                self.showErrorMessage(__('Invalid request destination URL.'));

                return false;
            }

            var resultContainer = self.getResultContainerEl(),
                listEl = self.getContactListsEl(),
                testButton = self.getTestButtonEl();

            testButton.attr('disabled', true);

            $.ajax({
                url: self.listsUrl,
                data: self.getRequestParams(),
                method: 'POST',
                showLoader: true,
                dataType: 'json',
                complete: function (jqXHR, textStatus) {
                    testButton.attr('disabled', false);
                },
                success: function (response) {
                    if ('success' !== response.result) {
                        return false;
                    }

                    if (0 !== response.info.length) {
                        if (0 !== listEl.find('option').length) {
                            return false;
                        }

                        response.info.forEach(function (option) {
                            listEl.append('<option selected value="' + option.key + '">' + option.value + '</option>');
                        });
                    } else {
                        resultContainer.css('color', '#fa9932');
                        resultContainer.append('<br>');
                        resultContainer.append(self.getWarningTextForEmptyLists());
                    }
                },
                error: function (response) {
                    self.showErrorMessage(__('Something went wrong.') + '' + response.status + ':' + response.statusText);
                }
            });
        };

        /**
         * Retrieve prepared Object
         *
         * @return {{class: string, style: string, text: string}}
         */
        self.getResultContainerParams = function () {
            return {
                class: 'message',
                style: 'background: none;',
                text: '<b>' + __('Undefined result for Test Connection!') + '</b>'
            };
        };

        /**
         * Retrieve prepared Object for success results
         *
         * @return {{class: string, style: string, text: string}}
         */
        self.getResultContainerSuccessParams = function () {
            var resultParams = self.getResultContainerParams();
            resultParams.class += ' message-success success';
            resultParams.style += ' color: green;';
            resultParams.text = '<b>' + __('Connection successful!') + '</b>';

            return resultParams;
        };

        /**
         * Retrieve prepared Object for error results
         *
         * @return {{class: string, style: string, text: string}}
         */
        self.getResultContainerErrorParams = function () {
            var resultParams = self.getResultContainerParams();
            resultParams.class += ' message-error error';
            resultParams.style += ' color: red;';
            resultParams.text = '<b>' + __('Connection Error!') + '</b>';

            return resultParams;
        };

        /**
         * Show error message via alert component
         * @param message
         */
        self.showErrorMessage = function (message) {
            if (! message) {
                message = __('Something went wrong.');
            }

            alert({
                title: __('Error Processing Test Connection'),
                content: message
            });
        };

        /**
         * Retrieve warning text
         *
         * @return {string}
         */
        self.getWarningTextForEmptyLists = function () {
            return __('However, newsletter subscription will not work until you create at least one Contact List.');
        };

        /**
         * Validate serviceId
         *
         * @return {boolean}
         */
        self.isValidServiceId = function () {
            return (self.serviceId && (0 !== String(self.serviceId).length));
        };

        /**
         * Validate URL
         *
         * @param url
         * @return {boolean}
         */
        self.isValidUrl = function (url) {
            return /^https?:\/\/[^\/]/.test(url);
        };

        /**
         * Validate infoUrl
         *
         * @return {boolean}
         */
        self.isValidInfoUrl = function () {
            return self.isValidUrl(self.infoUrl);
        };

        /**
         * Validate listUrl
         *
         * @return {boolean}
         */
        self.isValidListsUrl = function () {
            return self.isValidUrl(self.listsUrl);
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getFieldsetEl = function () {
            return $('fieldset#prnewsletterpopup_integration_' + self.serviceId);
        };

        /**
         * Retrieve string identifier
         *
         * @return {string}
         */
        self.getResultContainerId = function () {
            return self.resultContainerPrefix + self.serviceId;
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getResultContainerEl = function () {
            return $('#' + self.getResultContainerId());
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getApiUrlEl = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_url');
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getApiKeyEl = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_key');
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getAppNameEl = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_app_name');
        };

        /**
         *
         * @return {*|jQuery.fn.init|r.fn.init|n.fn.init|jQuery|HTMLElement}
         */
        self.getApiTempSecret = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_secret_id');
        };

        /**
         * @return {*|r.fn.init|jQuery.fn.init|n.fn.init|jQuery|HTMLElement}
         */
        self.getApiAccountID = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_account_id');
        };

        /**
         *
         * @return {*|n.fn.init|jQuery.fn.init|r.fn.init|jQuery|HTMLElement}
         */
        self.getApiUserID = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_user_id');
        };

        /**
         *
         * @return {*|n.fn.init|jQuery.fn.init|r.fn.init|jQuery|HTMLElement}
         */
        self.getApiClientFolderID = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_client_folder_id');
        };

        /**
         *
         * @return {*|n.fn.init|jQuery.fn.init|r.fn.init|jQuery|HTMLElement}
         */
        self.getApiPassword = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_api_password');
        };

        /**
         *
         * @return {*|n.fn.init|jQuery.fn.init|r.fn.init|jQuery|HTMLElement}
         */
        self.getApiSecurityToken = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_api_security_token');
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getTestButtonEl = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_test_connection');
        };

        /**
         * @return {*|jQuery.fn.init|n.fn.init|m.fn.init|jQuery|HTMLElement}
         */
        self.getContactListsEl = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_list')
        };

        /**
         *
         * @return {*|jQuery.fn.init|r.fn.init|n.fn.init|jQuery|HTMLElement}
         */
        self.getApiClientId = function () {
            return $('#prnewsletterpopup_integration_' + self.serviceId + '_client_id');
        };

        /**
         * Validate single HTML element
         *
         * @param element
         * @return {boolean}
         */
        self.validateSingleFormElement = function (element) {
            if (element instanceof jQuery) {
                if (element.length <= 0) {
                    return true;
                }

                element = element.get(0);
            }

            return Validation.validate(element);
        };

        /**
         * Validate form elements
         *
         * @return {boolean}
         */
        self.isValidFormElements = function () {
            var isValid = self.validateSingleFormElement(self.getApiUrlEl());
            isValid = self.validateSingleFormElement(self.getAppNameEl()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiKeyEl()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiUserID()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiAccountID()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiClientFolderID()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiPassword()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiSecurityToken()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiTempSecret()) && isValid;
            isValid = self.validateSingleFormElement(self.getApiClientId()) && isValid;

            return isValid;
        };

        /**
         *Retrieve required data for all requests
         *
         * @return {{app_name: *, api_temp_secret: *, api_url: *, api_key: *, api_account_id: *, service_id: string, api_user_id: *}}
         */
        self.getRequestParams = function () {
            var apiUrlEl = self.getApiUrlEl(),
                apiKeyEl = self.getApiKeyEl(),
                appNameEl = self.getAppNameEl(),
                apiTempSecret = self.getApiTempSecret(),
                apiAccountId = self.getApiAccountID(),
                apiclientFolderId = self.getApiClientFolderID(),
                apiUserId = self.getApiUserID(),
                apiPassword = self.getApiPassword(),
                apiSecurityToken = self.getApiSecurityToken();

            return {
                api_url: apiUrlEl.length ? apiUrlEl.val() : '',
                api_key: apiKeyEl.length ? apiKeyEl.val() : '',
                app_name: appNameEl.length ? appNameEl.val() : '',
                api_temp_secret: apiTempSecret.length ? apiTempSecret.val() : '',
                api_account_id: apiAccountId.length ? apiAccountId.val() : '',
                api_client_folder_id: apiclientFolderId.length ? apiclientFolderId.val() : '',
                api_user_id: apiUserId.length ? apiUserId.val() : '',
                api_password: apiPassword.length ? apiPassword.val() : '',
                api_security_token: apiSecurityToken.length ? apiSecurityToken.val() : '',
                service_id: self.serviceId,
            };
        }
    }

    /**
     * Create new API connector
     */
    var APIConnector = new APIConnectorClass();

    window.testApiConnection = function (url, serviceId, loadListUrl) {
        /**
         * Set connector params
         */
        APIConnector.serviceId = serviceId;
        APIConnector.infoUrl = url;
        APIConnector.listsUrl = loadListUrl;

        /**
         * Test connection
         */
        APIConnector.testConnection();
    };

    window.openWindowToGetConstantContactToken = function (url) {
        window.open(url, 'Constant Contact', "width=500px, height=500px");
    };
});
