define([
    "prototype"
], function (urlR, urlE) {
    AiLogsInfo = Class.create();
    AiLogsInfo.prototype = {
        initialize: function () {
            this.data = {};
        },
        setInfo: function (requestInfoUrl, sendEmailsUrl) {
            this.data['requestInfoUrl'] = requestInfoUrl;
            this.data['sendEmailsUrl'] = sendEmailsUrl;
        },
        getInfo: function (type) {
            return this.data[type];
        }
    };

    return AiLogsInfo;
});
