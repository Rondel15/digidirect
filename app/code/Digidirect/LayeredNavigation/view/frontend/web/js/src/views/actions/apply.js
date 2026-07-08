import $ from 'jquery';
import View from './../index';

const FILTERS_DELIMITER = 'filters',
    URL_DELIMITER = '/';

export default class Action {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        this.watchers(this.options);
    }

    watchers (options) {
        let postfix = options.postfix;

        $(document).on('click', options.selectors.applyButton, () => {
            var $blockFilters = $(options.selectors.blockFilters),
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
                extraStringParams = this.getExtraParameter(),
                newUrl;

            urlPart = this.removeLastSlash(urlPart);
            urlPart = this.removePostfix(urlPart, postfix);

            if (!$selectedLinks.length && !$selectedSwatches.length) {
                return false;
            }

            urlParameters = this.processUrl($selectedSwatches, urlParameters);
            urlParameters = this.processUrl($selectedLinks, urlParameters);

            stringParams = this.makeUrlParameters(urlParameters.parameters);

            if (options.enabledSeoUrls) {
                seoParts = urlParameters.seoParts;
                if (stringParams) {
                    stringParams = this.normalizeUrl(stringParams);
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

            View.sendRequest(options, {
                url: newUrl,
                type: 'apply'
            });
        });
    }

    /**
     * Normalize URL
     * @param urlString
     * @returns {string}
     */
    normalizeUrl (urlString) {
        return URL_DELIMITER + urlString.replace(/[=&]+/g, URL_DELIMITER);
    }

    /**
     *
     * @param paramsArrayMain
     * @returns {string}
     */
    makeUrlParameters (paramsArrayMain) {
        let newArray = [];
        for (var attributeCode in paramsArrayMain) {
            if (!paramsArrayMain.hasOwnProperty(attributeCode) || attributeCode == 'undefined') {
                continue;
            }
            newArray.push(attributeCode + '=' + paramsArrayMain[attributeCode].join(','));
        }
        return newArray.join('&');
    }

    /**
     * Process URL
     * @param selectedElements
     * @param urlParametersObject
     * @returns {{parameters: *, seoParts: (string|*)}}
     */
    processUrl (selectedElements, urlParametersObject) {
        var self = this,
            paramsArrayMain = urlParametersObject.parameters,
            seoParts = urlParametersObject.seoParts;

        selectedElements.each(function (index, element) {
            var $element = $(element),
                attributeCode = $element.data('code'),
                attributeValue = $element.data('value'),
                seoPart = $element.data('seo');

            const ATTRIBUTE_CODE_SEPARATOR = ',';

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

    /**
     * Remove Last Slash
     * @param string
     */
    removeLastSlash (string) {
        return this.removeEndSymbol(string, '/');
    }

    /**
     * Remove End Symbol
     * @param string
     * @param symbol
     * @returns {*}
     */
    removeEndSymbol (string, symbol) {
        var position = string.lastIndexOf(symbol);
        if (position == string.length - symbol.length) {
            string = string.substr(0, position);
        }
        return string;
    }

    /**
     * Remove postfix
     * @param urlPart
     * @param postfix
     * @returns {*}
     */
    removePostfix (urlPart, postfix) {
        return this.removeEndSymbol(urlPart, postfix);
    }

    /**
     * Get URL parameter by name
     * @param name
     * @returns {string|null}
     */
    getParameterByName (name) {
        return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(window.location.search) || [null, ''])[1].replace(/\+/g, '%20')) || null;
    }

    /**
     * Get Extra URL parameters
     * @returns {string}
     */
    getExtraParameter () {
        let extraParameterArray = ['q', 'product_list_mode', 'product_list_order', 'product_list_dir'],
            params = '',
            i = 0;
        extraParameterArray.forEach((element) => {
            if (this.getParameterByName(element)) {
                if (i > 0) {
                    params += '&';
                }
                params += element + '=' + this.getParameterByName(element);
                i++;
            }
        });

        return params;
    }
}
