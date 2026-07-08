define([
    'jquery',
    'domReady!',
    'mage/translate'
], function ($) {
    'use strict';

    var elementExpect = setInterval(function () {
        if ($("input[name='delivery_number']").length) {
            clearInterval(elementExpect);
            init();
        }
    }, 100);

    var childElement, elemWrapper;

    function init() {
        childElement = document.querySelector("input[name='delivery_number']");
        makeWrapper(childElement);
        elemWrapper = document.getElementsByClassName('wrapper-order')[0];
        listen(elemWrapper,childElement);
    }

    function makeWrapper (child) {
        var parent = document.createElement('div');
        parent.classList.add('wrapper-order');
        child.setAttribute('placeholder', $.mage.__('Purchase Order Number'));
        child.parentNode.insertBefore(parent, child);
        parent.appendChild(child);
    }

    function listen(elem, child) {
        elem.addEventListener('click', function () {
            if (!elem.classList.contains('-open')) {
                elem.classList.add('-open');
                $(child).click(function (event) {
                    event.stopImmediatePropagation();
                });
            } else {
                elem.classList.remove('-open');
            }
        })
    }
});

