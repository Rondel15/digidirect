/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


define([
        'jquery',
        'jquery/list-filter'
        ], function ($) {
            function main($) {
                var $element = $(element);
                 var YOUR_URL_HERE = config.AjaxUrl;
                $(document).on('click','generate-file',function() {
                        var param = 'ajax=1';
                            $.ajax({
                                showLoader: true,
                                url: YOUR_URL_HERE,
                                data: param,
                                type: "POST",
                                dataType: 'json'
                            }).done(function (data) {
                               
                            });
                    });
            };
        return main;
    });