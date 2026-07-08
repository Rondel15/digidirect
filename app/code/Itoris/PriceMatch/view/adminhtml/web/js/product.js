/**
 * Copyright © 2018 ITORIS INC. All rights reserved.
 * See license agreement for details
 */
require(["jquery"], function ($) {
    $(function () {
        $("body").on("change", "#itoris_pricematch_group_list", function(e){
            var options = e.target.options;
            var opt;
            var result = [];

            for (var i=0, iLen=options.length; i<iLen; i++) {
                opt = options[i];

                if (opt.selected) {
                    result.push(opt.value || opt.text);
                }
            }

            $("#itoris_pricematch_group_list_hidden").val(result);
        });
    });
});