import $ from 'jquery';
import {loadAddOn} from 'digidirectUtils';
import {Store, Events} from './truncate-collection/common/store';
import 'jquery/ui';
import 'truncateDotdotdot';
import 'domReady!';

$.widget('digidirect.truncateCollection', {
    version: '0.0.1',
    options: {
        selector: '.product-item-link',
        parsedSelector: '[data-truncate-default-text]',
        childOptions: {}
    },
    _create() {
        this.store = Store;
        this.events = Events;
        this.$element = $(this.element);
        this._loadAddOn()
            ._bindEventsListener();
        //pull out of the stream and put it at the end of the queue
        setTimeout(() => {
            this.truncateRawСollection();
        }, 0);
    },
    _bindEventsListener() {
        this.store.on(this.events.TRUNCATE_RAW_COLLECTION, () => {
            //pull out of the stream and put it at the end of the queue
            setTimeout(() => {
                this.truncateRawСollection();
            }, 0);
        });

        return this;
    },
    _loadAddOn() {
        let options = this.options,
            store = this.store,
            events = this.events;
        loadAddOn({options, store, events}, this, 'Truncate Collection');
        return this;
    },
    _setOptions(key, value) {
        this._super("_setOption", key, value);
    },
    destroy() {
        $.Widget.prototype.destroy.call(this);
    },
    getRawСollection() {
        return $(this.element).find(this.options.selector).not(':digidirect-truncateDotdotdot');
    },
    truncateRawСollection() {
        this.getRawСollection().each((index, element) => {
            $(element).truncateDotdotdot(this.options.childOptions);
        });

        return this;
    }
});

export default $.digidirect.truncateCollection;