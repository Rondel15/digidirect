import $ from 'jquery';
import View from './../index';

export default class Toolbar {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        let self = this;

        // Override Magento_Catalog/js/product/list/toolbar changeUrl method
        $.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters,
                i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined
                    ? decode(parameters[1].replace(/\+/g, '%20'))
                    : '';
            }
            paramData[paramName] = paramValue;

            $.each(paramData, function (index) {
                if (baseUrl.indexOf(index) !== -1) {
                    let pattern = new RegExp('/' + index + '/[^./]*', 'g');
                    baseUrl = baseUrl.replace(pattern, '');
                }
            });

            paramData = $.param(paramData);

            // Remove second toolbar (bottom) to prevent double call
            View.sendRequest(self.options, {
                url: baseUrl + (paramData.length ? '?' + paramData : ''),
                type: paramName,
                value: paramValue
            });
        };

        $(document).on('click', '.toolbar-products .pages a', function (e) {
            e.preventDefault();
            View.sendRequest(self.options, {
                url: $(this).attr('href'),
                type: 'pager'
            });
        });
    }
}
