/**
 * Copyright © 2018 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
define([
    'jquery',
    'Magento_Catalog/js/price-utils',
    'Magento_Swatches/js/swatch-renderer',
    'Magento_Ui/js/modal/modal',
    'mage/translate',
    'mage/mage'
], function ($, priceUtils) {
    'use strict';

    var element = function (config, element) {

        $(function () {
            var form                 = $("#itoris-pm-modal-form"),
                parentLink           = $(".itoris-pm-product-marker"),
                selectorMessagePopup = '[data-itoris-placeholder="messages"]',
                selectorMessage      = '[data-placeholder="messages"]',
                containersProductQty = new Object(),
                mainParam            = new Object();


            form.mage('validation', {});

            var bootstrap = function (param, linkPlace) {
                
                var elem = parentLink.clone(),
                    elemSupporting;

                linkPlace.append(elem);

                var showLink = function () {
                    elem.show();
                };

                var hidenLink = function () {
                    elem.hide();
                };

                var equalObjects = function (obj1, obj2) {
                    return $.map(obj1, function(v, k) {
                        return obj2[k] && obj2[k] == v ? true : false;
                    }).indexOf(false) < 0;
                };

                if (!param['name'] && !param['email']) {
                    $("#itoris_pm_modal_name").show();
                    $("#itoris_pm_modal_fname").show();
                    $("#itoris_pm_modal_lname").show();
                    $("#itoris_pm_modal_contact").show();
                    $("#itoris_pm_modal_email").show();
                }else {
                    mainParam['name'] = param['name'];
                    mainParam['email'] = param['email'];
                    $("#itoris_pm_modal_name_static").text(param['name']).parent().show();
                    $("#itoris_pm_modal_email_static").text(param['email']).parent().show();
                    $("#itoris_pm_modal_name_requred").hide();
                    $("#itoris_pm_modal_email_requred").hide();
                }

                showLink();

                $(elem).on('click', function () {
                    updateFormId(param['productId']);
                    updateFormPrice(param['final_price']);
                    updateFormName(param['product_name']);
                    $("#itoris-pm-modal").show().modal({
                        buttons: [{
                            text: $.mage.__('Submit'),
                            class: '',
                            click: function() {
                                $("#itoris-pm-modal [generated=true].mage-error").remove();
                                $("#itoris-pm-link").css("pointer-events", 'none');
                                $("#itoris-pm-link-custom").css("pointer-events", 'none');
                                form.submit();
                            }
                        }],
                        modalClass: 'itoris-pm-modal',
                        title: $.mage.__('Price Match Request')

                    }).modal('openModal');
                });
            };

            var updateFormId = function(newValue) {
                $('#itoris_pm_modal_product_id').val(newValue);
            }

            var updateFormPrice = function(newValue) {
                $("#itoris_pm_modal_final_price").text(priceUtils.formatPrice(newValue, config['priceFormat']));
                $("#itoris-pm-currency").text(config['priceFormat'].pattern.replace('%s', ''));
                $("#itoris_pm_modal_match_price").attr('data-itoris-current-price', newValue);
            };

            var updateFormName = function(newValue) {
                $("#itoris_pm_modal_product_name").text(newValue);
            };

            var addMessage = function(data) {

                if (data.status == 'OK') {
                    $(selectorMessage).html(
                        "<div class='messages'><div class='message-success success message'><span>"+data.msg+"</span></div></div>"
                    );
                    $(selectorMessagePopup).html(
                        "<div class='messages'><div class='message-success success message'><span>"+data.msg+"</span></div></div>"
                    );
                } else if(data.status == 'ERROR') {
                    $(selectorMessage).html(
                        "<div class='messages'><div class='message-error error message'><span>"+data.msg+"</span></div></div>"
                    );
                    $(selectorMessagePopup).html(
                        "<div class='messages'><div class='message-error error message'><span>"+data.msg+"</span></div></div>"
                    );
                }
            };

            form.submit(function() {
                var checkValidName = true,
                    checkValidEmail = true,
                    checkValidContact = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_contact")),
                    checkValidPrice = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_match_price")),
                    checkValidUrl = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_match_url"));

                if (!mainParam['name'] && !mainParam['email']) {
                    checkValidName = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_name"));
                    checkValidEmail = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_email"));
                }
                
                checkValidContact = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_contact"));

                if (checkValidName && checkValidContact && checkValidEmail && checkValidPrice && checkValidUrl) {
                    var dataPost = {
                        id      : $("#itoris_pm_modal_product_id").val(),
                        //name    : $("#itoris_pm_modal_name").val(),
                        name    : $("#itoris_pm_modal_fname").val() + " " +  $("#itoris_pm_modal_lname").val(),
                        email   : $("#itoris_pm_modal_email").val(),
                        price   : $("#itoris_pm_modal_match_price").val(),
                        url     : $("#itoris_pm_modal_match_url").val(),
                        comment : $("#itoris_pm_modal_comment").val() + "#contact_number:" + $("#itoris_pm_modal_contact").val()
                    };

                    $("#itoris-pm-modal").modal('closeModal');
                    $(".itoris-custom-main-grid .mage-error").hide();
                    $.ajax({
                        url         : config['urlAdd'],
                        type        : "POST",
                        data        : dataPost,
                        showLoader  : true,
                            success : function(data) {
                                addMessage(data);
                                $("#itoris-pm-link").css("pointer-events", 'auto');
                                $("#itoris-pm-link-custom").css("pointer-events", 'auto');

                                if(!mainParam['name'] && !mainParam['email']){
                                    $("#itoris_pm_modal_name").val('');
                                    $("#itoris_pm_modal_fname").val('');
                                    $("#itoris_pm_modal_lname").val('');
                                    $("#itoris_pm_modal_contact").val('');
                                    $("#itoris_pm_modal_email").val('');
                                }
                                $("#itoris_pm_modal_match_price").val('');
                                $("#itoris_pm_modal_match_url").val('');
                                $("#itoris_pm_modal_comment").val('');
                            }
                        });
                    } else {
                        $("div.mage-error:not(:first-of-type)").remove();
                        $("#itoris-pm-link").css("pointer-events", 'auto');
                        $("#itoris-pm-link-custom").css("pointer-events", 'auto');
                    }

                    return false;
            });

            $('div.table-wrapper.grouped table.grouped tbody').find('tr td.item div.price-box').each(function() {
                var currentProductId = $(this).attr('data-product-id');
                var item =[];

                containersProductQty[currentProductId] = $(this);

                $.ajax({
                    url: config['urlBootstrap'],
                    type: "POST",
                    data: {
                        id  : currentProductId,
                        sid : config['storeId']
                    },
                    success: function (data) {
                        item['productId']    = currentProductId;
                        item['name']         = data['name'];
                        item['email']        = data['email'];
                        item['product_name'] = data['product_name'];
                        item['final_price']  = data['final_price'];

                        if (data['check_render_link']) {
                            bootstrap(item, containersProductQty[currentProductId]);
                        }
                    }
                });
            });

        });
    };

    return element;
});

