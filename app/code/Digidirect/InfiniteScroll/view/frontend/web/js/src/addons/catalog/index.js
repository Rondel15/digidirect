import {GlobalStore, GlobalEvents} from 'digidirectStoreCatalog';
import {Store, Events} from './../../common/store';

export default class AddOn {
    constructor (options) {
        GlobalStore.on(GlobalEvents.PRODUCT_COLLECTION_UPDATED, (data) => {
            if (data.page_params.nextUrl) {
                data.page_params.nextUrl = data.page_params.nextUrl + '&_is=' + options.perPageCount;
                if (!(data.page_params.nextUrl.indexOf('product_list_dir') !== -1) && (options.nextUrl.indexOf('product_list_dir') !== -1)) {
                    let direction = this.getParameterByName('product_list_dir', options.nextUrl);
                    data.page_params.nextUrl = data.page_params.nextUrl + '&product_list_dir=' + direction;
                }
            }
            this.options = Object.assign({}, options, data.page_params);
            Store.emit(Events.RELOAD, this.options);
        });
    }

    getParameterByName (name, url) {
        if (!url) url = window.location.href;
        name = name.replace(/[\[\]]/g, '\\$&');
        var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
            results = regex.exec(url);
        if (!results) return null;
        if (!results[2]) return '';
        return decodeURIComponent(results[2].replace(/\+/g, ' '));
    }
}
