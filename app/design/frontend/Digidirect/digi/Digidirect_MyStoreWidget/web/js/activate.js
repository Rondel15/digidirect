define([
    'jquery'
], function ($) {

    // Delete blue hover background on SDD modal window
    $(document).on('hover', '#info-store-item', function () {
        $('.link.ui-corner-all').css('background', 'none');
    });

    // Function for add green icon to SDD modal window
    $(document).on('click', '.link-choose-btn', function () {
        $('.link-choose-btn').css('display', 'flex');
        $('.choosed-sign').css('display', 'flex');
        $('#mystore-control ul').css('display', 'block');
        $(this).css('display', 'none');
        $('.sdd-loader').css('display', 'block');
        $('#mystore-control ul').css('pointer-events', 'none');
        setTimeout(function () {
            $('#mystore-form .search-mode-btn').click();
        }, 1000)
    });
});
