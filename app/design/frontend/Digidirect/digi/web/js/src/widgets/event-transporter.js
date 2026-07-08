import $ from 'jquery';
import 'jquery/ui';
import 'domReady!';

$.widget('digidirect.eventTransporter', {
    version: '0.0.1',
    options: {
        triggerEventList: 'click',
        invokedEvent: 'click',
        customerEventSelector: false,
    },
    _create() {
        this.$element = $(this.element);

        this._bindEvents();
    },
    _bindEvents() {
        this.$element.on(this.options.triggerEventList, (event) => {
            event.preventDefault();
            $(this.options.customerEventSelector).trigger(this.options.invokedEvent);
        });
    },
    destroy() {
        $.Widget.prototype.destroy.call(this);
    },
});

export default $.digidirect.eventTransporter;
