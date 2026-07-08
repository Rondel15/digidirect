import Model from './../models/index';
import {Store, Events} from './store';
import {loadView} from 'digidirectUtils';

export default class Component {
    constructor (options, mode) {
        this.options = Object.assign({}, this.options, options);
        this.model = new Model(this.options);

        this.bind(this.options, mode);
        this.watchers();
    }

    bind (options, mode) {
        switch (mode) {
            case 'post':
                loadView(options, this, 'Digidirect_AddressVerification/js/dist/views/post', 'Address Verification (Post)');
                break;
            default:
                loadView(options, this, 'Digidirect_AddressVerification/js/dist/views/index', 'Address Verification (Google API)');
        }
    }

    async fetchData (url, fetchOptions, response = undefined, value) {
        try {
            if (!url) {
                return;
            }

            // start progress
            Store.emit(Events.POST_FETCH_DATA_PROGRESS);

            // model fetch data
            let data = await this.model.fetchData(url, fetchOptions);

            Store.emit(Events.POST_FETCH_DATA_SUCCESS, data, response, value);
        } catch (error) {
            console.warn('Address Verification request failed', error);
            Store.emit(Events.POST_FETCH_DATA_ERROR, error);
        }
    }

    watchers () {
        let self = this;
        Store.on(Events.POST_FETCH_DATA_START, (url, data, response, value) => {
            url += self.getParams(data);
            this.fetchData(url, {
                method: 'GET'
            }, response, value);
        });
    }

    getParams (data) {
        let params = '';
        if (data === undefined || Object.keys(data).length === 0) {
            return '';
        }
        Object.keys(data).forEach(function (key) {
            if (params !== '') {
                params += '&';
            }
            params += key + '=' + encodeURIComponent(data[key]);
        });
        return '?' + params;
    }
}
