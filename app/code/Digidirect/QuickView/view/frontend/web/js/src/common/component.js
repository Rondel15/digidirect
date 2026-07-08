import Model from './../models/index';
import {Store, Events} from './store';
import {loadView} from 'digidirectUtils';

export default class Component {
    constructor (options) {
        this.options = options;
        this.model = new Model(this.options);
        this.bind(this.options);
        this.watchers(this.options);
    }

    bind (options) {
        loadView(options, this, 'Digidirect_QuickView/js/dist/view/index', 'Quick View');
    }

    watchers (options) {
        Store.on(Events.DATA_FETCH_START, (url) => {
            if (Store.currentState === Events.DATA_FETCH_PROGRESS) {
                return true;
            }
            this.fetchData(url);
        });    
    }

    async fetchData (url) {
        Store.emit(Events.DATA_FETCH_PROGRESS);
        try {
            if (!url) {
                return true;
            }            
            let data = await this.model.fetchData(url);
            Store.emit(Events.DATA_FETCH_SUCCESS, data);
        } catch (error) {
            console.warn('Quick View request failed', error);
            Store.emit(Events.ERROR, error);
        }
        Store.emit(Events.DATA_FETCH_FINISH);
    }
}
