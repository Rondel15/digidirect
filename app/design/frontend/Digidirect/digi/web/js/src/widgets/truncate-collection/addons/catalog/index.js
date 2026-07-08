import {Store as GlobalStore, Events as GlobalEvents} from 'Digidirect_InfiniteScroll/js/dist/common/store';

export default class AddOn {
    constructor({options, ..._store}) {
        this.Store = _store.store;
        this.Events = _store.events;

        GlobalStore.on(GlobalEvents.DATA_FETCH_SUCCESS, () => {
            this.Store.emit(this.Events.TRUNCATE_RAW_COLLECTION);
        });
        GlobalStore.on(GlobalEvents.DATA_FETCH_FINISH, () => {
            this.Store.emit(this.Events.TRUNCATE_RAW_COLLECTION);
        });
        GlobalStore.on(GlobalEvents.RELOAD, () => {
            this.Store.emit(this.Events.TRUNCATE_RAW_COLLECTION);
        });
    }
}