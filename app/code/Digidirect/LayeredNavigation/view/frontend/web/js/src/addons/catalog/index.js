import {GlobalStore, GlobalEvents} from 'digidirectStoreCatalog';
import {Store, Events} from './../../common/store';

export default class AddOn {
    constructor () {
        Store.on(Events.DATA_FETCH_SUCCESS, (data) => {
            GlobalStore.emit(GlobalEvents.PRODUCT_COLLECTION_UPDATED, data);
        });

        Store.on(Events.DATA_FETCH_START, (data, options) => {
            GlobalStore.emit(GlobalEvents.PRODUCT_COLLECTION_UPDATE_START, data, options);
        });
    }
}
