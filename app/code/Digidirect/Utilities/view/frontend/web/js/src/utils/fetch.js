/* global fetch, Headers */
import './../../vendor/whatwg-fetch';
/**
 * Call fetch
 * @param url
 * @param options
 * @param status
 * @param response
 */
export function callFetch (url, options, status = checkFetchStatus, response = getFetchResponse) {
    if (typeof options == 'undefined') {
        options = setHeaders();
    } else {
        options = Object.assign({}, setHeaders(), options);
    }

    return fetch(url, options).then(status).then(response);
}

function checkFetchStatus (response) {
    if (response.status >= 200 && response.status < 300) {
        return response;
    } else {
        var error = new Error(response.statusText);
        error.response = response;
        throw error;
    }
}

function getFetchResponse (response) {
    return response.json();
}

function setHeaders () {
    let defaultHeaders = new Headers({
        'Accept': 'application/json'
    });

    return {
        headers: defaultHeaders,
        credentials: 'include'
    };
}
