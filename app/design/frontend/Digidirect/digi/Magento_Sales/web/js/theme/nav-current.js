define([
    'domReady!'
], function () {
    'use strict';

    var linksElem = document.querySelector('.links-navigation'),
        wrapperElem = linksElem.querySelector('.content'),
        titleElem,
        navElement = wrapperElem.querySelectorAll('.nav.item');

    if (linksElem && navElement && navElement.length === 1) {
        titleElem = linksElem.querySelector('.title');
        titleElem.classList.add('-one-option');
    }
});