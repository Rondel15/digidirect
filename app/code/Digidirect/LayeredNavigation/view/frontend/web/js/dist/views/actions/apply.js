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

    var _createClass = function () {
        function defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }

        return function (Constructor, protoProps, staticProps) {
            if (protoProps) defineProperties(Constructor.prototype, protoProps);
            if (staticProps) defineProperties(Constructor, staticProps);
            return Constructor;
        };
    }();

    var FILTERS_DELIMITER = 'filters',
        URL_DELIMITER = '/';

    var Action = function () {
        function Action(options) {
            _classCallCheck(this, Action);

            this.options = Object.assign({}, this.options, options);
            this.watchers(this.options);
        }

        _createClass(Action, [{
            key: 'watchers',
            value: function watchers(options) {
                var _this = this;

                var postfix = options.postfix;

                (0, _jquery2.default)(document).on('click', options.selectors.applyButton, function () {
                    var $blockFilters = (0, _jquery2.default)(options.selectors.blockFilters),
                        urlPart = options.baseUrl,
                        paramsPart = window.location.search.substr(1) || '',
                        $selectedSwatches = $blockFilters.find(options.selectors.selectedSwatches),
                        $selectedLinks = $blockFilters.find(options.selectors.selectedLinks),
                        seoParts = '',
                        urlParameters = {
                        parameters: [],
                        seoParts: seoParts
                    },
                        stringParams,
                        extraStringParams = _this.getExtraParameter(),
                        newUrl;

                    urlPart = _this.removeLastSlash(urlPart);
                    urlPart = _this.removePostfix(urlPart, postfix);

                    if (!$selectedLinks.length && !$selectedSwatches.length) {
                        return false;
                    }

                    urlParameters = _this.processUrl($selectedSwatches, urlParameters);
                    urlParameters = _this.processUrl($selectedLinks, urlParameters);

                    stringParams = _this.makeUrlParameters(urlParameters.parameters);

                    if (options.enabledSeoUrls) {
                        seoParts = urlParameters.seoParts;
                        if (stringParams) {
                            stringParams = _this.normalizeUrl(stringParams);
                        }

                        newUrl = urlPart + URL_DELIMITER + FILTERS_DELIMITER + seoParts + stringParams + postfix;
                        if (paramsPart) {
                            newUrl += '?' + extraStringParams;
                        }
                    } else {
                        newUrl = urlPart + postfix;

                        if (extraStringParams) {
                            if (stringParams) {
                                stringParams = extraStringParams + '&' + stringParams;
                            } else {
                                stringParams = extraStringParams;
                            }
                        }

                        if (stringParams) {
                            newUrl += '?' + stringParams;
                        }
                    }

                    _index2.default.sendRequest(options, {
                        url: newUrl,
                        type: 'apply'
                    });
                });
            }
        }, {
            key: 'normalizeUrl',
            value: function normalizeUrl(urlString) {
                return URL_DELIMITER + urlString.replace(/[=&]+/g, URL_DELIMITER);
            }
        }, {
            key: 'makeUrlParameters',
            value: function makeUrlParameters(paramsArrayMain) {
                var newArray = [];
                for (var attributeCode in paramsArrayMain) {
                    if (!paramsArrayMain.hasOwnProperty(attributeCode) || attributeCode == 'undefined') {
                        continue;
                    }
                    newArray.push(attributeCode + '=' + paramsArrayMain[attributeCode].join(','));
                }
                return newArray.join('&');
            }
        }, {
            key: 'processUrl',
            value: function processUrl(selectedElements, urlParametersObject) {
                var self = this,
                    paramsArrayMain = urlParametersObject.parameters,
                    seoParts = urlParametersObject.seoParts;

                selectedElements.each(function (index, element) {
                    var $element = (0, _jquery2.default)(element),
                        attributeCode = $element.data('code'),
                        attributeValue = $element.data('value'),
                        seoPart = $element.data('seo');

                    var ATTRIBUTE_CODE_SEPARATOR = ',';

                    if (self.options.enabledSeoUrls && seoPart && attributeCode) {
                        if (index !== 0 && attributeCode === selectedElements.eq(index - 1).data('code')) {
                            seoParts += ATTRIBUTE_CODE_SEPARATOR + seoPart;
                        } else {
                            seoParts += URL_DELIMITER + attributeCode + URL_DELIMITER + seoPart;
                        }
                    } else {
                        if (paramsArrayMain[attributeCode] !== undefined && $element.data('multiselect')) {
                            if (paramsArrayMain[attributeCode].indexOf(attributeValue) == -1) {
                                paramsArrayMain[attributeCode].push(attributeValue);
                            }
                        } else {
                            paramsArrayMain[attributeCode] = [attributeValue];
                        }
                    }
                });

                return {
                    parameters: paramsArrayMain,
                    seoParts: seoParts
                };
            }
        }, {
            key: 'removeLastSlash',
            value: function removeLastSlash(string) {
                return this.removeEndSymbol(string, '/');
            }
        }, {
            key: 'removeEndSymbol',
            value: function removeEndSymbol(string, symbol) {
                var position = string.lastIndexOf(symbol);
                if (position == string.length - symbol.length) {
                    string = string.substr(0, position);
                }
                return string;
            }
        }, {
            key: 'removePostfix',
            value: function removePostfix(urlPart, postfix) {
                return this.removeEndSymbol(urlPart, postfix);
            }
        }, {
            key: 'getParameterByName',
            value: function getParameterByName(name) {
                return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(window.location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
            }
        }, {
            key: 'getExtraParameter',
            value: function getExtraParameter() {
                var _this2 = this;

                var extraParameterArray = ['q', 'product_list_mode', 'product_list_order', 'product_list_dir'],
                    params = '',
                    i = 0;
                extraParameterArray.forEach(function (element) {
                    if (_this2.getParameterByName(element)) {
                        if (i > 0) {
                            params += '&';
                        }
                        params += element + '=' + _this2.getParameterByName(element);
                        i++;
                    }
                });

                return params;
            }
        }]);

        return Action;
    }();

    exports.default = Action;
    module.exports = exports['default'];
});
