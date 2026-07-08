define(['exports', './../../vendor/whatwg-fetch'], function (exports) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });
    exports.callFetch = callFetch;

    /**
     * Call fetch
     * @param url
     * @param options
     * @param status
     * @param response
     */
    function callFetch(url, options) {
        var status = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : checkFetchStatus;
        var response = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : getFetchResponse;

        if (typeof options == 'undefined') {
            options = setHeaders();
        } else {
            options = Object.assign({}, setHeaders(), options);
        }

        return fetch(url, options).then(status).then(response);
    } /* global fetch, Headers */


    function checkFetchStatus(response) {
        if (response.status >= 200 && response.status < 300) {
            return response;
        } else {
            var error = new Error(response.statusText);
            error.response = response;
            throw error;
        }
    }

    function getFetchResponse(response) {
        return response.json();
    }

    function setHeaders() {
        var defaultHeaders = new Headers({
            'Accept': 'application/json'
        });

        return {
            headers: defaultHeaders,
            credentials: 'include'
        };
    }
});
