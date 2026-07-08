define(['ko'], function (ko) {
    'use strict';

    return {
        items: ko.observableArray([]),
        settings: ko.observable({}),
        pager: ko.observableArray([]),
        currentPage: ko.observable(1)
    };
});
