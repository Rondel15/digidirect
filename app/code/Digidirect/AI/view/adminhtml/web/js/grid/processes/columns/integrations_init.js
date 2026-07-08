define([
    "prototype"
], function (urlR, urlE) {
    AiIntegrationsInit = Class.create();
    AiIntegrationsInit.prototype = {
        initialize: function () {
            this.data = {};
            this.loaderImg = null;
        },
        setInfo: function (requestParamsUrl, runIntegrationUrl , deleteScheduleUrl , logDataUrl) {
            this.data['requestParamsUrl'] = requestParamsUrl;
            this.data['runIntegrationUrl'] = runIntegrationUrl;
            this.data['deleteScheduleUrl'] = deleteScheduleUrl;
            this.data['logDataUrl'] = logDataUrl;
        },
        getInfo: function (type) {
            return this.data[type];
        }
    };

    return AiIntegrationsInit;
});
