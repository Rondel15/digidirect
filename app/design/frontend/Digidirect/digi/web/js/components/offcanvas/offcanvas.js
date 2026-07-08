define([
    'jquery',
    'js/components/offcanvas/dist/common/component',
    'jquery/ui'
], function ($, Component) {
    'use strict';

    $.widget('digidirect.offcanvas', {
        options: {
            offCanvasWrapperSelector: 'body',
            styleClasses: {
                initialisedClass: '-offcanvas-inited',
                activeClass: '-active',
                wrapperClass: 'offcanvas-wrapper',
                rightDirectionClass: 'offcanvas-right',
                leftDirectionClass: 'offcanvas-left',
                offcanvasOpenedClass: '-offcanvas-opened'
            },
            trigger: '',
            addOn: '',
            closeOnEsc: true,
            moveBodyOnOpen: false
        },

        _create: function () {
            this.options.triggerElementSelector = this.element;
            new Component(this.options, this.element);
        }
    });

    return $.digidirect.offcanvas;
});
