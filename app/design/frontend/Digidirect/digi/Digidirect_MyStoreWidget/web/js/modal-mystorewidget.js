define([
    'jquery',
    'jquery/ui',
    'Magento_Ui/js/modal/modal'
], function ($) {
    $.widget('digi.modalMystorewidget', {
        _create: function () {
            var wrapper = this.element;
            wrapper.modal({
                type: 'popup',
                responsive: true,
                innerScroll: false,
                modalClass: 'modal-mystore-widget-wrapper',
                buttons: []
            });
            $(".mystore-button").click(function (e) {
                e.preventDefault();
                wrapper.modal("openModal");
            });
        }
    });
    return $.digi.modalMystorewidget;
});
