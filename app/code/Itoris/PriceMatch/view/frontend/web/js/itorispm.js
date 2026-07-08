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
            var bootstrap = function (param) {
                var form = $("#itoris-pm-modal-form"),
                    //opts = $(".product-options-wrapper .swatch-opt .swatch-attribute"),
                    opts = $(".product-options-wrapper .swatch-opt .swatch-attribute,.product-options-wrapper .configurable .super-attribute-select"),
                    elem = $(".itoris-pm-product-marker"),
                    elemSupporting,
                    super_attribute = {},
                    productNameChildren;

                form.mage('validation', {});

                // if (opts.length) {
                //      elemSupporting = $(".product-info-main #product-options-wrapper .swatch-opt,.product-info-main #product-options-wrapper .configurable");
                //      elemSupporting.after(elem);
                // } else {
                //      elemSupporting =  elem.siblings('.product-info-price');
                //      elemSupporting.before(elem);
                // }

                elemSupporting =  elem.siblings('.product-add-form');
                elemSupporting.after(elem);

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

                var getSuperAttributeName = function(e) {
                    var name = e.getAttribute('attribute-id');
                    if (name)
                        return name;
                    else
                        return e.getAttribute('name').match(/super_attribute\[([0-9]{1,})\]/)[1];
                };

                var getSuperAttributeValue = function(e) {
                    if (e.getAttribute('option-selected'))
                        return e.getAttribute('option-selected');
                    else
                        return $(e).val();
                };

                opts.each(function(i,e) {
                    super_attribute[getSuperAttributeName(e)] = getSuperAttributeValue(e);
                });

                var checkShowLink = function() {
                    var checkVisible = true;

                    opts.each(function(i,e) {
                        if (!getSuperAttributeValue(e))
                            checkVisible = false;
                    });

                    return checkVisible;
                };

                var selectorMessagePopup = '[data-itoris-placeholder="messages"]';
                var selectorMessage = '[data-placeholder="messages"]';

                var addMessage = function (data) {

                    if(data.status == 'OK'){
                        $(selectorMessage).html(
                            "<div class='messages'><div class='message-success success message'><span>"+data.msg+"</span></div></div>"
                        );
                        $(selectorMessagePopup).html(
                            "<div class='messages'><div class='message-success success message'><span>"+data.msg+"</span></div></div>"
                        );
                    }else if(data.status == 'ERROR'){
                        $(selectorMessage).html(
                            "<div class='messages'><div class='message-error error message'><span>"+data.msg+"</span></div></div>"
                        );
                        $(selectorMessagePopup).html(
                            "<div class='messages'><div class='message-error error message'><span>"+data.msg+"</span></div></div>"
                        );
                    }
                };

                var updadeConfigurablePrice = function () {
                    var key = null,
                        cfConfig = JSON.parse(config['cfConfig']),
                        listIndex = cfConfig['index'],
                        price = null;

                    if(cfConfig['optionPrices']){
                        for (var item in listIndex){
                            if( equalObjects(listIndex[item],super_attribute) )
                                key =  item;
                        }

                        if(cfConfig['optionPrices'] && cfConfig['optionPrices'][key] && cfConfig['optionPrices'][key]['finalPrice']){
                            price = cfConfig['optionPrices'][key]['finalPrice']['amount'];
                            if (cfConfig['optionPrices'][key]['basePrice'] && cfConfig['optionPrices'][key]['basePrice']['amount']) {
                                window.mpTaxRate = price / cfConfig['optionPrices'][key]['basePrice']['amount'];
                            }
                            $("#itoris_pm_modal_final_price").text(priceUtils.formatPrice(price, config['priceFormat']));
                            $("#itoris_pm_modal_match_price").attr('data-itoris-current-price', price);
                        }
                    }
                };

                var updadeConfigurableSimpleName = function () {
                    function isCurrentName(productParam) {
                        if (typeof productParam == 'undefined')
                            return false;

                        if (typeof productParam.attribute == 'undefined')
                            return false;

                        if (!equalObjects(productParam.attribute,super_attribute))
                            return false;

                        return true;
                    }

                    var configNames = config['configurable_children'];

                    for(var cfName in  configNames ){
                        if (isCurrentName(configNames[cfName])) {
                            $("#itoris_pm_modal_product_name").text(configNames[cfName]['product_name']);
                            break;
                        }
                    }
                };
                
                var _price = param['final_price'], priceBoxPrice = jQuery('[data-role="priceBox"][data-product-id="'+config['productId']+'"] [data-price-type="finalPrice"]').attr('data-price-amount');
                window.mpTaxRate = 1;
                if (priceBoxPrice) {
                    if (_price) window.mpTaxRate = priceBoxPrice / _price;
                    _price = priceBoxPrice;
                }
                $("#itoris_pm_modal_final_price").text(priceUtils.formatPrice(_price, config['priceFormat']));
                $("#itoris_pm_modal_match_price").attr('data-itoris-current-price', _price);

                $("#itoris-pm-currency").text( config['priceFormat'].pattern.replace('%s', '') );
                if(!param['name'] && !param['email']){
                    $("#itoris_pm_modal_name").show();
                    $("#itoris_pm_modal_fname").show();
                    $("#itoris_pm_modal_lname").show();
                    $("#itoris_pm_modal_contact").show();
                    $("#itoris_pm_modal_email").show();
                }else {
                    $("#itoris_pm_modal_name_static").text(param['name']).parent().show();
                    $("#itoris_pm_modal_email_static").text(param['email']).parent().show();
                    $("#itoris_pm_modal_name_requred").hide();
                    $("#itoris_pm_modal_email_requred").hide();
                }

                if (opts.length) {
                    if( checkShowLink() ){
                        updadeConfigurablePrice();
                        updadeConfigurableSimpleName();
                        showLink();
                    }
                    MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
                    opts.each(function (i,e) {
                        var mutationHandler = function(mutations) {
                            super_attribute[getSuperAttributeName(e)] = getSuperAttributeValue(e);
                            if (checkShowLink()) {
                                updadeConfigurablePrice();
                                updadeConfigurableSimpleName();
                                showLink();
                            } else {
                                hidenLink();
                            }
                        }
                        var observer = new MutationObserver(mutationHandler);
                        observer.observe(e, {attributes: true});
                        $(e).change(mutationHandler);
                    });
                } else if (!opts.length){
                    showLink();
                }

                form.submit(function(){
                    var checkValidName = true,
                        checkValidEmail = true,
                        checkValidContact = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_contact")),
                        checkValidPrice = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_match_price")),
                        checkValidUrl = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_match_url"));

                    if(!param['name'] && !param['email']){
                        checkValidName = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_name"));
                        checkValidEmail = $.validator.validateSingleElement(document.getElementById("itoris_pm_modal_email"));
                    }
                    //$(".itoris-custom-main-grid .mage-error").remove();
                    //$("#itoris-pm-modal [generated=true].mage-error").remove();
                    if(checkValidName && checkValidContact && checkValidEmail && checkValidPrice && checkValidUrl){
                        var dataPost = {
                            id: config['productId'],
                            //name: $("#itoris_pm_modal_name").val(),
                            name: $("#itoris_pm_modal_fname").val() + " " +  $("#itoris_pm_modal_lname").val(),
                            email: $("#itoris_pm_modal_email").val(),
                            price: $("#itoris_pm_modal_match_price").val() / window.mpTaxRate,
                            url: $("#itoris_pm_modal_match_url").val(),
                            comment: $("#itoris_pm_modal_comment").val() + "#contact_number:" + $("#itoris_pm_modal_contact").val()
                        };

                        if( !$.isEmptyObject(super_attribute) ){
                            dataPost['super_attribute'] = JSON.stringify(super_attribute);
                        }
                        $("#itoris-pm-modal").modal('closeModal');
                        //$(".itoris-custom-main-grid .mage-error").hide();
                        $.ajax({
                            url: config['urlAdd'],
                            type: "POST",
                            data: dataPost,
                            showLoader: true,
                            success: function(data){
                                addMessage(data);
                                $("#itoris-pm-link").css("pointer-events", 'auto');
                                $("#itoris-pm-link-custom").css("pointer-events", 'auto');

                                if(!param['name'] && !param['email']){
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
                    }else{
                        $("div.mage-error:not(:first-of-type)").remove();
                        $("#itoris-pm-link").css("pointer-events", 'auto');
                        $("#itoris-pm-link-custom").css("pointer-events", 'auto');
                    }

                    return false;
                });


                $(element).on('click', 'a', function () {
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

            var item =[];
            $.ajax({
                url: config['urlBootstrap'],
                type: "POST",
                data: {id: config['productId'], sid: config['storeId']},
                success: function (data) {
                    item['name'] = data['name'];
                    item['email'] = data['email'];
                    item['product_name'] = data['product_name'];
                    item['final_price'] = data['final_price'];

                    if(data['check_render_link']){
                        bootstrap(item);
                    }

                }
            });

        });
    };

    return element;
});
