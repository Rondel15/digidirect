/* global Headers */
import {callFetch} from 'digidirectUtils';

export default class Model {
    constructor () {
        this.fetchData = this.getData;
    }

    getData (url) {
        if (typeof url !== 'string') {
            return false;
        }
        return callFetch(url, this.setOptions(), undefined, this.getFetchResponseHtml);
    }

    getFetchResponseHtml (response) {
        return response.text();
    }

    setOptions () {
        return {
            method: 'GET',
            headers: new Headers({
                'Accept': 'text/html'
            })
        };
    }
}
