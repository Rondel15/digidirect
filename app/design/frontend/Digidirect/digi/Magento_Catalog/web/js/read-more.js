define([
    'jquery',
    'matchMedia',
    'tabs',
    'domReady!',
    'mousewheel',
    'mCustomScrollbar'
], function ($, mediaCheck) {
    'use strict';

    var action = document.querySelectorAll('.tab-in'),
        maxHeight = 715,
        tabElement = document.getElementById('details-tab');

    Array.prototype.forEach.call(action, function (el) {
        var control = el.getAttribute('data-alias'),
            tab = document.getElementById(control),
            button = tab.querySelector('.read-more');
        checkElement(tab);

        el.addEventListener('click', function () {
            var control = el.getAttribute('data-alias'),
                tab = document.getElementById(control);
            checkElement(tab);
        });

        button.addEventListener('click', toggleBar);

        function toggleBar() {
            if (tab.classList.contains('-closed')) {
                tab.classList.remove('-closed');
            } else {
                tab.classList.add('-closed');
            }
        }

        function checkElement(tab) {
            var height = tab.offsetHeight;

            if (height > maxHeight) {
                tab.classList.add('more');
            }
        }
    });

    mediaCheck({
        media: '(min-width: 1024px)',
        entry: function () {
            $('.right-bar').mCustomScrollbar({
                theme: 'dark-3'
            });
        },
        exit: function () {
            $('.right-bar').mCustomScrollbar('destroy');
        }
    });

    mediaCheck({
        media: '(min-width: 768px)',
        entry: function () {
            $(tabElement).tabs({
                "openedState": "active",
                "active": 0,
                "collapsible": false
            });
        },
        exit: function () {
            $(tabElement).tabs({
                "active": false,
                "collapsible": true
            });
        }
    });
});