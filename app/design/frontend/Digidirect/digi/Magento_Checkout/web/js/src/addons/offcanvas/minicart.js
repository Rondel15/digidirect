import {Store as GlobalStore, Events as GlobalEvents} from 'digidirectStoreCheckout';

export default class AddOn {
    constructor({options, ..._store}) {
        this.Store = _store.store;
        this.Events = _store.events;
        this.element = options.triggerElementSelector.first();
        GlobalStore.on(GlobalEvents.MINICART_TOGGLE, (data, state) => {
            this.Store.emit(this.Events.OFFCANVAS_TOOGLE, data || this.element, state || this.Store.currentState);
        });
        GlobalStore.on(GlobalEvents.MINICART_OPEN, (data) => {
            this.Store.emit(this.Events.OFFCANVAS_OPEN, data || this.element);
        });
        GlobalStore.on(GlobalEvents.MINICART_CLOSE, (data) => {
            this.Store.emit(this.Events.OFFCANVAS_CLOSE, data || this.element);
        });
    }
}
