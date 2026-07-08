define([
    'jquery'
], function ($) {
    'use strict';

    $(document).on('contentUpdated.isNeedUpdateSocial', function () {
        twttr.widgets.load();
    });
});
