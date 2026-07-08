'use strict';

define([
    'jquery',
    'ko',
    'ko_mapping',
    'text!Digidirect_AI/templates/grid/log/modal/loginfo.html',
    'Magento_Ui/js/modal/modal',
    'mage/validation'
], function ($, ko, mapping, infoTemplate) {
    var _helper = {
        i: 1,
        counter: function () {
            return this.i++;
        }
    };

    return {
        row: null,
        LogInfoModelView: mapping.fromJS({
            message: {text: false, state: false},
            id: false,
            download_link: false,
            details: false,
            created_at: false,
            mailfields: [0],
            connector_data: []
        }),
        show: function (data) {
            var $this = this,
                infoModal = $('<div/>').html(infoTemplate);

            //process row-data
            $this.row = data;

            //set data
            $this.LogInfoModelView.id($this.row.id);

            //show modal window
            infoModal.modal({
                title: 'Log #' + $this.row.id,
                type: 'slide',
                buttons: []
            }).trigger('openModal');

            //MODAL EVENT BINDS
            //add email field
            $this.LogInfoModelView.addMailField = function () {
                //increase fields array
                this.mailfields().push(_helper.counter());
                this.mailfields(this.mailfields());
            };

            //delete email field
            $this.LogInfoModelView.deleteMailField = function () {
                //remove email field
                var index = $this.LogInfoModelView.mailfields().indexOf(this);

                $this.LogInfoModelView.mailfields().splice(index, 1);
                $this.LogInfoModelView.mailfields($this.LogInfoModelView.mailfields());
            };

            //send form
            $this.LogInfoModelView.sendForm = function (form) {
                //validate data
                $(form).validation();
                if ($(form).validation('isValid')) {
                    //send emails
                    $.when($.ajax({
                        url: window.AiLogsInfo.getInfo('sendEmailsUrl'),
                        type: 'post',
                        data: $(form).serialize(),
                        showLoader: true
                    })).then(function (data, textStatus, jqXHR) {
                        //xhr success
                        if (!data.error) {
                            $this.LogInfoModelView.message.text(data.msg);
                            $this.LogInfoModelView.message.state('success');
                            return true;
                        }

                        $this.LogInfoModelView.message.text(data.msg);
                        $this.LogInfoModelView.message.state('error');
                    }, function (error) {
                        $this.LogInfoModelView.message.text(error.responseText);
                        $this.LogInfoModelView.message.state('error');
                    });
                }
            };

            //reset field
            $this.LogInfoModelView.message.text(false);
            $this.LogInfoModelView.message.state(false);

            $this.LogInfoModelView.details(false);
            $this.LogInfoModelView.download_link(false);

            //apply data
            ko.applyBindings($this.LogInfoModelView, infoModal[0]);

            //get Data
            this.getData();
        },
        getData: function () {
            var $this = this;

            //get data
            $.when($.ajax({
                url: window.AiLogsInfo.getInfo('requestInfoUrl'),
                type: 'get',
                data: {'log_id': $this.row.id},
                showLoader: true
            })).then(function (data, textStatus, jqXHR) {
                //xhr success
                if (!data.error) {
                    if (data.hasOwnProperty("data")) {
                        mapping.fromJS(data.data, {}, $this.LogInfoModelView);
                    }
                }
            });
        }
    };
});