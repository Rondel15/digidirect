define([
    'jquery'
], function ($) {
    'use strict';

    $(document).on('contentUpdated.isNeedUpdateSocial', function () {
        try {
            FB.XFBML.parse();
        } catch (ex) {
            console.warn(ex);
        }
    });
});
