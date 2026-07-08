'use strict';

define([
    'jquery',
    'ko',
    'ko_mapping',
    'text!Digidirect_AI/templates/grid/processes/modal/process_run.html',
    'mage/translate',
    'Magento_Ui/js/modal/modal',
    'mage/validation'
], function ($, ko, mapping, optionsTemplate, $t) {

    return {
        row: null,
        ajaxLoader: null,
        ajaxFlag: null,
        RunWithOptionsModelView: mapping.fromJS({
            message: {text: false, state: false},
            content: false,
            schedule_content: '',
            can_submit_run: false,
            process_code: '',
            log_msg: false,
            log_details: false,
            action: '',
            process_status: ''
        }),
        show: function (data) {
            var $this = this,
                infoModal = $('<div/>').html(optionsTemplate);

            function customFailCallback(error) {
                if (error.status === 504) {
                    $this.RunWithOptionsModelView.message.text($t('Session timeout, process continue in background...'));
                } else {
                    $this.RunWithOptionsModelView.message.text(error.responseText);
                }

                $this.RunWithOptionsModelView.message.state('error');
            }

            $('#process_info_popup').remove();

            //custom loader
            $this.ajaxLoader = infoModal.find('.ai_ajax_loader');

            //process row-data
            $this.row = data;

            //show modal window
            infoModal.modal({
                title: 'Process code : ' + $this.row.process_code,
                type: 'slide',
                closed: function () {
                    clearTimeout($this.ajaxFlag);
                },
                buttons: []
            }).trigger('openModal');

            //reset model
            $this.RunWithOptionsModelView.message.text(false);
            $this.RunWithOptionsModelView.message.state(false);
            $this.RunWithOptionsModelView.content('');
            $this.RunWithOptionsModelView.schedule_content(false);
            $this.RunWithOptionsModelView.can_submit_run(false);
            $this.RunWithOptionsModelView.log_msg(false);
            $this.RunWithOptionsModelView.log_details(false);
            $this.RunWithOptionsModelView.action(false);
            $this.RunWithOptionsModelView.process_status('');

            $this.RunWithOptionsModelView.aiCheckStateByInterval = function () {
                $this.ajaxLoader.show();

                $.when($.ajax({
                    url: window.AiIntegrationsInit.getInfo('logDataUrl'),
                    type: 'post',
                    data: {'process_code': $this.row.process_code},
                    showLoader: false
                })).then(function (data, textStatus, jqXHR) {

                    mapping.fromJS(data.data, {}, $this.RunWithOptionsModelView);
                    $this.updateGlobalMessage(data);

                    $this.ajaxLoader.hide();

                    //xhr success
                    if (!data.global.error) {
                        if (data.data.action && data.data.action === 'start_update_process_status') {
                            $this.ajaxFlag = setTimeout(function () {
                                $this.RunWithOptionsModelView.aiCheckStateByInterval();
                            }, 10000);
                        }
                        return true;
                    }

                }, function (error) {
                    $this.RunWithOptionsModelView.message.text(data.global.msg);
                    $this.RunWithOptionsModelView.message.state('error');
                });

                return false;
            };

            $this.RunWithOptionsModelView.deleteScheduledRun = function () {
                clearTimeout($this.ajaxFlag);

                $.when($.ajax({
                    url: window.AiIntegrationsInit.getInfo('deleteScheduleUrl'),
                    type: 'post',
                    data: $('#run_options_data').serialize(),
                    showLoader: true
                })).then(function (data, textStatus, jqXHR) {
                    //xhr success

                    mapping.fromJS(data.data, {}, $this.RunWithOptionsModelView);

                    $this.updateGlobalMessage(data);

                    if (!data.global.error) {
                        $this.RunWithOptionsModelView.message.state('success');
                        $this.RunWithOptionsModelView.schedule_content(false);

                        return true;
                    }
                }, customFailCallback);
            };

            $this.RunWithOptionsModelView.sendRunOptionsForm = function (form) {
                $(form).validation();
                if ($(form).validation('isValid')) {
                    clearTimeout($this.ajaxFlag);

                    $.when($.ajax({
                        url: window.AiIntegrationsInit.getInfo('runIntegrationUrl'),
                        type: 'post',
                        data: $('#run_options_data').serialize(),
                        showLoader: true
                    })).then(function (data, textStatus, jqXHR) {

                        mapping.fromJS(data.data, {}, $this.RunWithOptionsModelView);
                        $this.updateGlobalMessage(data);

                        if (!data.global.error) {
                            $this.RunWithOptionsModelView.message.state('success');

                            if (data.data.action === 'start_update_process_status') {
                                $this.RunWithOptionsModelView.aiCheckStateByInterval();
                            }
                            return true;
                        }
                    }, customFailCallback);
                }

                return false;
            };

            //apply data
            ko.applyBindings($this.RunWithOptionsModelView, infoModal[0]);
            //get Data
            this.getData();
        },
        getData: function () {
            var $this = this;
            //get data
            $.when($.ajax({
                url: window.AiIntegrationsInit.getInfo('requestParamsUrl'),
                type: 'post',
                data: {'process_code': $this.row.process_code},
                showLoader: true
            })).then(function (data, textStatus, jqXHR) {
                //xhr success
                if (data.hasOwnProperty("data")) {
                    mapping.fromJS(data.data, {}, $this.RunWithOptionsModelView);

                    if (data.data.action === 'start_update_process_status') {
                        $this.RunWithOptionsModelView.aiCheckStateByInterval();
                    }
                }
            }, function () {
            });
        },
        updateGlobalMessage: function (data) {
            var message = data.global.msg;

            //update message state
            if (!data.global.error) {
                this.RunWithOptionsModelView.message.state('success');
            } else {
                this.RunWithOptionsModelView.message.state('error');
            }

            //clean message block
            this.RunWithOptionsModelView.message.text(message);

            if (message) {
                document.getElementById("ai_message_typed").innerHTML = message;
            }
        }
    };
});
