define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/storage',
    'Magento_Customer/js/customer-data'
], function ($, Component, ko, storage, customerData) {

    'use strict';

    return Component.extend({
        defaults: {
            template: 'Digidirect_Qantas/qantas/customer-form',
            visible: true
        },

        initialize: function () {
            this._super();
        },

        /**
         * 
         * @return {*|String}
         */
        getPromotionPoints: function () {
            var promoPoints = window.checkoutConfig.qantas_bonus_points;
            var basePoints = window.checkoutConfig.qantas_base_points;
            var points = parseInt(promoPoints) + parseInt(basePoints);

            if (promoPoints !== null && basePoints !== null) {
                return points;
            } else if (basePoints !== null) {
                return basePoints;
            } else {
                return 2;
            }

        },

        /**
         * @return {*|String}
         */
        validate: function () {
            var login_state = window.isCustomerLoggedIn;
            var ajaxRequest;
            var qff_form = $("#qff-form");
            var qff_number = $("#qff_number");
            var qff_lastname = $("#qff_lastname");
            var qff_trigger = $("#qff-trigger");
            var qff_error = $(".qff-link-error");
            
            if (login_state) {
                var action_url = "/customer/account/editpost";
                $('#qff_number').val(window.customerData["qff_number"]);
                $('#qff_lastname').val(window.customerData["qff_lastname"]);

                if ($(".qff-link-error").length > 0) {
                    $(".qff-link-error").remove();
                }
                
            } else {
                var action_url = "/customer/index/validate";
            }

            $("#qff-trigger").click(function () {
                ajaxRequest = $.ajax({
                    showLoader: false,
                    url: action_url,
                    data: {
                        qff_number: $('#qff_number').val(),
                        qff_lastname: $('#qff_lastname').val(),
                        qff_action: "qff_action"
                    },
                    type: "POST",
                    dataType: 'json',

                    beforeSend: function () {

                        $("#qff-trigger").html("<span>Please wait</span>");
                        $("#qff-trigger").attr("disabled", true);

                        $('#qff_number').attr("readonly", true);
                        $('#qff_lastname').attr("readonly", true);

                    }
                });


                ajaxRequest.done(function (data) {
                    console.log(data);
                    if (data.result) {
                        if ($(".qff-link-error").length > 0) {
                            $(".qff-link-error").remove();
                        }

                        if ($(".qff-link-success").length > 0) {
                            $(".qff-link-success").remove();
                        }
                        var success_message = "<span class='qff-link-success'>Validation success.</span>";
                        $(success_message).insertAfter($('#qff_lastname'));

                        $('#qff_number').attr("readonly", true);
                        $('#qff_lastname').attr("readonly", true);
                        $("#qff-trigger").attr("data-action", "change");
                        $("#qff-trigger").html("<span>Change</span>");
                    } else {

                        if ($(".qff-link-error").length > 0) {
                            $(".qff-link-error").remove();
                        }

                        if ($(".qff-link-success").length > 0) {
                            $(".qff-link-success").remove();
                        }

                        var error_message = "<span class='qff-link-error'>Details does not match our record. Please enter the correct details.</span>";
                        $(error_message).insertAfter($('#qff_lastname'));

                        //$("#qff-trigger").attr("data-action", "link");
                        $("#qff-trigger").html("<span>Validate</span>");
                        $("#qff-trigger").attr("disabled", false);
                        $('#qff_number').attr("readonly", false);
                        $('#qff_lastname').attr("readonly", false);
                    }
                });


                ajaxRequest.fail(function () {
                    $("#qff-trigger").attr("disabled", false);

                    if ($(".qff-link-error").length > 0) {
                        $(".qff-link-error").remove();
                    }

                    if ($(".qff-link-success").length > 0) {
                        $(".qff-link-success").remove();
                    }
                    var error_message = "<span class='qff-link-error'>Details does not match our record. Please enter the correct details.</span>";
                    $(error_message).insertAfter($('#qff_lastname'));

                    $("#qff-trigger").attr("data-action", "link");
                    $("#qff-trigger").html("<span>Validate</span>");

                });
            });

        }
    });
});
