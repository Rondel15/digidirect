import {GlobalStore, GlobalEvents} from 'digidirectStoreCatalog';

export default class AddOn {
    constructor ({options, ..._store}) {
        this.Store = _store.store;
        this.Events = _store.events;
        GlobalStore.on(GlobalEvents.PRODUCT_COLLECTION_UPDATE_START, (data) => {
            this.Store.emit(this.Events.OFFCANVAS_CLOSE);
        });        
    }
}
