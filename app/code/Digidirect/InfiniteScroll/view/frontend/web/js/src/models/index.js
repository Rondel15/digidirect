import {callFetch} from 'digidirectUtils';

export default class Model {
    constructor (options) {
        this.fetchData = this.getData;
    }

    getData (url, options) {
        if (typeof url !== 'string') {
            return false;
        }
        if (options === undefined || Object.keys(options).length === 0) {
            return callFetch(url);
        }
        return callFetch(url, options);
    }
}
