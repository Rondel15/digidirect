import {loadView, loadAddOn} from 'digidirectUtils';
import EventPubSubExtended from 'eventPubSubExtended';
import * as Constants from './constants';

export default class Component {
    constructor (options, element) {
        this.options = options;
        this.element = element;
        this.store = new EventPubSubExtended(Constants);
        this.events = Constants;
        this.bind();
    }

    bind () {
        this._loadView();
        this._loadAddOn();
    }

    /**
     * Load view
     * @private
     */
    _loadView () {
        let options = this.options,
            store = this.store,
            events = this.events,
            element = this.element;
        loadView({options, store, events, element}, this, './js/components/offcanvas/dist/views/index', 'OffCanvas');
    }

    /**
     * Load addOn
     * @private
     */
    _loadAddOn () {
        let options = this.options,
            store = this.store,
            events = this.events;
        loadAddOn({options, store, events}, this, 'OffCanvas');
    }
}
