import $ from 'jquery';
import customerData from 'Magento_Customer/js/customer-data';
import {Store, Events} from './../common/store';
import 'loader';
import 'Magento_Ui/js/modal/modal';

export default class View {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        this.init();
        this.watchers(this.options);
        this.bind();
    }

    /**
     *  Init loader, init popup
     */
    init () {
        Object.assign(this.options.modal, {closed: this.closed});
        this.loader = $(this.options.loaderContainer).loader({'icon': this.options.loaderIcon, 'texts': {'loaderText': this.options.loaderText}});
        this.popup = $(this.options.contentContainer).modal(this.options.modal);
        this.iframe = $(this.options.iframe);
        this.iframeIsLoaded = false;
    }

    /**
     *  Init watchers
     */
    watchers () {
        Store.on(Events.DATA_FETCH_PROGRESS, () => this.progress());
        Store.on(Events.DATA_FETCH_SUCCESS, (data) => this.success(data));
        Store.on(Events.DATA_FETCH_FINISH, () => this.finish());
        Store.on(Events.QUICK_VIEW_OPEN, () => this.open());
        Store.on(Events.QUICK_VIEW_OPENED, () => this.opened());
        Store.on(Events.QUICK_VIEW_CLOSE, () => this.close());
        Store.on(Events.QUICK_VIEW_CLOSED, () => this.closed());
        Store.on(Events.SECTION_UPDATE, (section) => this.updateSectionData(section));
        Store.on(Events.ADD_TO_CART, (context, options, form) => this.addToCart(context, options, form));
    }

    /**
     *  Init listeners
     */
    bind () {
        let self = this;
        $(document).on('click', this.options.button, function () {
            let url = $(this).data('url');
            if (url !== self.activeUrl) {
                Store.emit(Events.DATA_FETCH_START, url);
            } else {
                self.open();
            }
            self.activeUrl = url;
        });

        //  Mobile listeners
        if (this.options.isMobileEnabled) {
            this.mobileBind();
        }

        //  Transfer the Store and Events to the iframe, even if the iframe rebooted
        this.iframe.on('load', ()=> {
            this.delegateStore();
            if (this.isTouchDevice() && this.iframeIsLoaded) {
                this.bindTouchObserver();
            }
            this.iframeIsLoaded = true;
        });
    }

    /**
     *  Init listers on mobile divices
     */
    mobileBind () {
        let self = this;
        $(document).on('click', this.options.mobileListener, function (e) {
            let button = $(this).closest(self.options.itemSelector).find(self.options.button);
            if (button.is(':hidden')) {
                button.trigger('click');
                e.preventDefault();
            }
        });
    }

    /**
     *  Bind events for touch devices
     */
    bindTouchObserver () {
        let iframeObserveElement = this.iframe.contents().find('#maincontent')[0],
            iframeWindow = $(this.iframe[0].contentWindow),
            observer = new MutationObserver(function () {
                iframeWindow.trigger('resize');
            });

        observer.observe(iframeObserveElement, {
            attributes: true,
            subtree: true,
            attributeFilter: ['style']
        });

        iframeWindow.on('resize', $.proxy(this.setIframeHeight, this));
    }

    /**
     *  Set the iframe's height for touch devices
     */
    setIframeHeight () {
        $(this.iframe).height(this.iframe.contents().find('body').height());
    }

    /**
     *  Show loader
     */
    progress () {
        this.loader.trigger('processStart');
        this.clearIframe();
    }

    /**
     *  Insert content into iframe
     *  @param data {object}
     */
    success (data) {
        this._renderData(data);
        this.open();
        this._historyIframe();
    }

    /**
     *  Hide loader
     */
    finish () {
        this.loader.trigger('processStop');
    }

    /**
     *  Delegate Events and Store to iframe
     */
    delegateStore () {
        this.iframe[0].contentWindow.globalEvents = Events;
        this.iframe[0].contentWindow.globalStore = Store;
    }

    /**
     * Render content
     * @param data {object}
     * @private
     */
    _renderData (data) {
        let iframe = this.iframe[0].contentDocument.open();
        iframe.write(data);
        iframe.close();
    }

    /**
     * Substitute the url in iframe
     * @private
     */
    _historyIframe () {
        this.iframe[0].contentWindow.history.pushState('', '', this.activeUrl);
    }

    /**
     *  Update section data
     */
    updateSectionData (section) {
        customerData.reload(section, true);
    }

    /**
     *  Clear iframe
     */
    clearIframe () {
        //  Clear iframe for correct work document.write()
        this.iframe.attr('src', 'about:blank');
        this.iframeIsLoaded = false;
    }

    addToCart (context, options, form) {
        var self = context;
        $(options.minicartSelector).trigger('contentLoading');
        self.disableAddToCartButton(form);

        $.ajax({
            url: form.attr('action'),
            data: form.serialize(),
            type: 'post',
            dataType: 'json',
            beforeSend: function () {
                if (self.isLoaderEnabled()) {
                    $('body').trigger(options.processStart);
                }
            },
            success: function (res) {
                if (self.isLoaderEnabled()) {
                    $('body').trigger(options.processStop);
                }

                if (res.backUrl) {
                    window.location = res.backUrl;
                    return;
                }
                if (res.messages) {
                    $(options.messagesSelector).html(res.messages);
                }
                if (res.minicart) {
                    $(options.minicartSelector).replaceWith(res.minicart);
                    $(options.minicartSelector).trigger('contentUpdated');
                }
                if (res.product && res.product.statusText) {
                    $(options.productStatusSelector)
                        .removeClass('available')
                        .addClass('unavailable')
                        .find('span')
                        .html(res.product.statusText);
                }
                self.enableAddToCartButton(form);
            }
        });
    }

    isTouchDevice () {
        return 'ontouchstart' in document.documentElement;
    }

    /**
     *  Open popup
     */
    open () {
        this.popup.modal('openModal');
    }

    opened () {
    }

    close () {
        this.popup.modal('closeModal');
    }

    closed () {
        if ($.localStorage.get('isChangedWishList')) {
            Store.emit(Events.SECTION_UPDATE, 'wishlist');
            $.localStorage.remove('isChangedWishList');
        }
    }
}
