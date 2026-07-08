import Model from './../models/index';
import {Store, Events} from './../common/store';
import {loadView} from 'digidirectUtils';

export default class Component {
    constructor (options) {
        this.options = options;
        this.model = new Model(this.options);

        loadView(this.options, this, 'Digidirect_LayeredNavigation/js/dist/views/index', 'LayeredNavigation');

        try {
            // dynamic load addOn
            if (this.options.addOn) {
                require(['./' + this.options.addOn], (AddOn) => {
                    new AddOn(this.options);
                });
            }
        } catch (e) {
            console.warn('LayeredNavigation: AddOn load failed', e);
        }

        this.watchers(this.options);
    }

    async fetchData (url, fetchOptions) {
        try {
            if (!url) {
                return;
            }

            // start progress
            Store.emit(Events.DATA_FETCH_PROGRESS);

            // model fetch data
            let data = await this.model.fetchData(url, fetchOptions);

            this.updateBrowserHistory(url);

            Store.emit(Events.DATA_FETCH_SUCCESS, data);
        } catch (error) {
            console.warn('LayeredNavigation request failed', error);
            Store.emit(Events.ERROR, error);
        }
    }

    watchers (options) {
        Store.on(Events.DATA_FETCH_START, (data) => {
            if (Store.currentState === Events.DATA_FETCH_PROGRESS) {
                return;
            }

            if (data.isFilterRemember) {
                let formData = new FormData();
                formData.append('ajax_navigation', 'true');

                this.fetchData(data.url, {
                    method: 'POST',
                    body: formData
                });
            } else {
                this.fetchData(data.url, {
                    method: 'GET'
                });
            }
        });
    }

    /**
     * Update browser history state
     * @param url
     */
    updateBrowserHistory (url) {
        const AJAX_PARAM = 'ajax_navigation=true';
        if (url.indexOf(AJAX_PARAM) > -1) {
            url = url.replace('&' + AJAX_PARAM, '');
            url = url.replace('?' + AJAX_PARAM, '?');
            url = url.replace('?&', '?');
            url = url.replace(/\?$/, '');
        }
        window.history.pushState({'url': url}, '', url);
    }
    
    static loadFilter (filterName, options) {
        const FILTERS_FOLDER = 'Digidirect_LayeredNavigation/js/dist/views/filters/';
        
        require(['./' + FILTERS_FOLDER + filterName], (Filter) => {
            new Filter(options);
        });
    }
}
