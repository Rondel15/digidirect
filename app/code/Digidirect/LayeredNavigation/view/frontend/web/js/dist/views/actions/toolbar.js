define(['module', 'exports', 'jquery', './../index'], function (module, exports, _jquery, _index) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _index2 = _interopRequireDefault(_index);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var Toolbar = function Toolbar(options) {
        _classCallCheck(this, Toolbar);

        this.options = Object.assign({}, this.options, options);
        var self = this;

        // Override Magento_Catalog/js/product/list/toolbar changeUrl method
        _jquery2.default.mage.productListToolbarForm.prototype.changeUrl = function (paramName, paramValue, defaultValue) {
            var decode = window.decodeURIComponent,
                urlPaths = this.options.url.split('?'),
                baseUrl = urlPaths[0],
                urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                paramData = {},
                parameters,
                i;

            for (i = 0; i < urlParams.length; i++) {
                parameters = urlParams[i].split('=');
                paramData[decode(parameters[0])] = parameters[1] !== undefined ? decode(parameters[1].replace(/\+/g, '%20')) : '';
            }
            paramData[paramName] = paramValue;

            _jquery2.default.each(paramData, function (index) {
                if (baseUrl.indexOf(index) !== -1) {
                    var pattern = new RegExp('/' + index + '/[^./]*', 'g');
                    baseUrl = baseUrl.replace(pattern, '');
                }
            });

            paramData = _jquery2.default.param(paramData);

            // Remove second toolbar (bottom) to prevent double call
            _index2.default.sendRequest(self.options, {
                url: baseUrl + (paramData.length ? '?' + paramData : ''),
                type: paramName,
                value: paramValue
            });
        };

        (0, _jquery2.default)(document).on('click', '.toolbar-products .pages a', function (e) {
            e.preventDefault();
            _index2.default.sendRequest(self.options, {
                url: (0, _jquery2.default)(this).attr('href'),
                type: 'pager'
            });
        });
    };

    exports.default = Toolbar;
    module.exports = exports['default'];
});
