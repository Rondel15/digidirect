define(['jquery'], 
function($){
    "use strict";
    return function initializeForm()
    {
        $(window).load(function() {
            var checkExist = setInterval(function() {
                if ($("#qff_number").length) {
                    clearInterval(checkExist);

                    qff_form = $("#qff-form"); 
                    qff_number = $("#qff_number"); 
                    qff_lastname = $("#qff_lastname"); 
                    qff_trigger = $("#qff-trigger");
                    qff_error = $(".qff-link-error");
                    function verifyQffDetails(action, action_url){

                        $.ajax(
                        {
                            url: action_url,
                            async: false,
                            data: {
                                qff_number: qff_number.val(),
                                qff_lastname: qff_lastname.val(),
                                qff_action: action
                            },
                            type: "POST",
                            dataType: "json",
                            beforeSend: function(){
                                $("#qff-trigger").html("<span>Please wait</span>");
                                $("#qff-trigger").attr("disabled", true);
                            },
                            success: function(data){

                                setTimeout(
                                function() 
                                {
                                    if(data.result){
                                        if($(".qff-link-error").length > 0){
                                            $(".qff-link-error").remove();
                                        }

                                        if($(".qff-link-success").length > 0){
                                            $(".qff-link-success").remove();
                                        }

                                        qff_number.attr("readonly", true);
                                        qff_lastname.attr("readonly", true);
                                        $("#qff-trigger").attr("data-action", "change");
                                        $("#qff-trigger").html("<span>Change</span>");

                                         var success_message = "<span class='qff-link-success'>Validation success.</span>";
                                         $(success_message).insertAfter(qff_lastname);
                                    }
                                    else{
                                        if($(".qff-link-error").length > 0){
                                            $(".qff-link-error").remove();
                                        }

                                         if($(".qff-link-success").length > 0){
                                            $(".qff-link-success").remove();
                                        }

                                        var error_message = "<span class='qff-link-error'>Details does not match our record. Please enter the correct details.</span>";
                                        $(error_message).insertAfter(qff_lastname);

                                        $("#qff-trigger").attr("data-action", "link");
                                        $("#qff-trigger").html("<span>Validate</span>");
                                    }

                                    $("#qff-trigger").attr("disabled", false);
                                }, 3000);
                            },
                            error: function(){
                                $("#qff-trigger").attr("disabled", false);

                                if($(".qff-link-error").length > 0){
                                    $(".qff-link-error").remove();
                                }

                                if($(".qff-link-success").length > 0){
                                        $(".qff-link-success").remove();
                                    }
                                var error_message = "<span class='qff-link-error'>Details does not match our record. Please enter the correct details.</span>";
                                $(error_message).insertAfter(qff_lastname);

                                $("#qff-trigger").attr("data-action", "link");
                                $("#qff-trigger").html("<span>Validate</span>");
                            }
                        });
                    }

                    if(login_state){
                        if(qff_error.length > 0){
                                qff_error.remove();
                            }

                        qff_number.val(window.customerData["qff_number"]);
                        qff_lastname.val(window.customerData["qff_lastname"]);


                    }

                    $("#qff-trigger").click(function(e)
                    {
                        if(qff_error.length > 0){
                            qff_error.remove();
                        }
                        if($(".qff-link-success").length > 0){
                           $(".qff-link-success").remove();
                        }

                        if($("#qff-trigger").attr("data-action") == "link"){
                            var action = "update";
                            if(login_state){
                                var action_url = "/customer/account/editpost";
                            }
                            else{
                                var action_url = "/customer/index/qffaction";
                            }

                            verifyQffDetails(action, action_url);
                              e.preventDefault();
                        }
                        else{
                            qff_number.attr("readonly", false);
                            qff_lastname.attr("readonly", false);
                            qff_trigger.attr("data-action", "link");
                            qff_trigger.html("<span>Validate</span>");
                        }
                    });
                }
             }, 100);
        });
    }
});