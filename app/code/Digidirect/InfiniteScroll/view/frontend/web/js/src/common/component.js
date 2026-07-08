import Model from './../models/index';
import {Store, Events} from './store';
import {loadView, loadAddOn} from 'digidirectUtils';

export default class Component {
    constructor (options) {
        this.options = options;
        this.model = new Model(this.options);

        this.bind(this.options);
        this.watchers(this.options);
    }

    bind (options) {
        loadView(options, this, 'Digidirect_InfiniteScroll/js/dist/views/index', 'Infinite Scroll');
        this.loadAddOn(options);
    }

    async fetchData (url, fetchOptions) {
        try {
            if (!url) {
                return true;
            }

            // start progress
            Store.emit(Events.DATA_FETCH_PROGRESS);

            // model fetch data
            let data = await this.model.fetchData(url, fetchOptions);

            Store.emit(Events.DATA_FETCH_SUCCESS, data);

            // Infinite scroll finish
            if (!data.url) {
                Store.emit(Events.DATA_FETCH_FINISH, data);
            }
        } catch (error) {
            console.warn('Infinite scroll request failed', error);
            Store.emit(Events.ERROR, error);
        }
    }

    watchers (options) {
        Store.on(Events.DATA_FETCH_START, (url) => {
            if (Store.currentState === Events.DATA_FETCH_PROGRESS) {
                return;
            }
            this.fetchData(url, options.requestOptions);
        });
    }

    loadAddOn (options) {
        loadAddOn(options, this, 'Infinite Scroll');
    }
}
