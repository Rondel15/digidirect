define([
    'jquery',
    'matchMedia',
    'domReady!',
    'jquery/ui',
    'mage/accordion'
], function ($, mediaCheck) {
    'use strict';

    mediaCheck({
        media: '(max-width: 1799px)',
        entry: function () {
            $("#productOverview").tabs({
                active: 1,
                openedState: "active",
                collapsible: true
            })
        },
        exit: function () {
            $("#productOverview").tabs({
                active: 0,
                openedState: "active",
                collapsible: false
            })
        },
    });
});

