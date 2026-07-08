import $ from 'jquery';
import 'domReady!';

export default class View {
    constructor ({options, ..._store}) {
        this.Store = _store.store;
        this.Events = _store.events;
        this.element = _store.element;
        this.options = options;
        this.bind();
    }

    bind () {
        this._checkElementsLength();
        this._initOffCanvas();
        this.actionsCall();
        this.watchers();
    }

    watchers () {
        this.Store.on(this.Events.OFFCANVAS_INITIALIZED, (data) => this.initialized(data));
        this.Store.on(this.Events.OFFCANVAS_OPEN, (data) => this.open(data));
        this.Store.on(this.Events.OFFCANVAS_OPENED, (data) => this.opened(data));
        this.Store.on(this.Events.OFFCANVAS_CLOSE, (data) => this.close(data));
        this.Store.on(this.Events.OFFCANVAS_CLOSED, (data) => this.closed());
        this.Store.on(this.Events.OFFCANVAS_TOOGLE, (data, state) => this.toggle(data, state));
    }

    /**
     * Check if selectors present on the page and load action if it is.
     * @private
     */
    _checkElementsLength () {
        this.selectors = {
            offCanvasWrapper: document.querySelector(this.options.offCanvasWrapperSelector),
            offCanvasPanel: $(this.element.data('container'))
        };

        if (this.selectors.offCanvasWrapper.length && this.selectors.offCanvasPanel.length) {
            throw new Error('Offcanvas Panel or Wrapper not found');
        }

        this._checkDirectionClasses();
    }

    /**
     * Check if directions classes present on panels
     * @private
     */
    _checkDirectionClasses () {
        this.options.rightPanelPresent = this.selectors.offCanvasPanel.hasClass(this.options.styleClasses.rightDirectionClass);
        this.options.leftPanelPresent = this.selectors.offCanvasPanel.hasClass(this.options.styleClasses.leftDirectionClass);

        if (!(this.options.rightPanelPresent || this.options.leftPanelPresent)) {
            throw new Error('None offcanvas direction classes found for offCanvas panels elements');
        }
    }

    /**
     * Add inited class to offCanvas wrapper when offCanvas has been inited and create local selectors
     * @private
     */
    _initOffCanvas () {
        this.selectors.offCanvasWrapper.classList.add(this.options.styleClasses.initialisedClass);
        if (this.options.moveBodyOnOpen) {
            this.selectors.offCanvasWrapper.classList.add('-offcanvas-overflow', this.options.styleClasses.wrapperClass);
        }

        this.Store.emit(this.Events.OFFCANVAS_INITIALIZED);
    }

    /**
     * Add event listener for trigger
     * @private
     */
    actionsCall () {
        // call offCanvas event on trigger element click
        this.element.on('click', (e) => {
            this.Store.emit(this.Events.OFFCANVAS_TOOGLE, $(e.currentTarget), this.Store.currentState);
        });

        if (this.options.trigger) {
            $(this.options.trigger).on('click', (e) => {
                e.preventDefault();
                this.Store.emit(this.Events.OFFCANVAS_TOOGLE, $(e.currentTarget), this.Store.currentState);
            });
        }

        // close offCanvas on Esc press if config is true
        if (this.options.closeOnEsc) {
            $(document).on('keyup', (e) => {
                if (e.keyCode === this.Events.KEYCODE_ESC && this.Store.currentState === this.Events.OFFCANVAS_OPENED) {
                    this.Store.emit(this.Events.OFFCANVAS_CLOSE, this.element);
                }
            });
        }
    }
    /**
     * Toggles appearance of offcanvas
     * @param element
     * @param currentState
     * @returns {boolean}
     */
    toggle (element, currentState) {
        if (currentState === this.Events.OFFCANVAS_OPENED) {
            // check if opened offcanvas should be closed and another one should NOT be opened
            if (element.hasClass(this.options.styleClasses.activeClass)) {
                this.Store.emit(this.Events.OFFCANVAS_CLOSE, element);
                return false;
            }
            this.Store.emit(this.Events.OFFCANVAS_CLOSE, element);
            return false;
        }
        this.Store.emit(this.Events.OFFCANVAS_OPEN, element);
    }

    /**
     * Show OffCanvas Element
     * @param element
     */
    open (element) {
        // add active class for trigger to show overlay
        element.addClass(this.options.styleClasses.activeClass);
        // add active class for panel and set aria-hidden
        $(element.data('container')).addClass(this.options.styleClasses.activeClass).attr('aria-hidden', 'false');
        // notify body that offcanvas opened
        document.querySelector('body').classList.add(this.options.styleClasses.offcanvasOpenedClass);
        // add defining class to offcanvas element if move body effect should be applied
        if (this.options.moveBodyOnOpen) {
            this.selectors.offCanvasWrapper.classList.add(element.data('direction'));
        }
        this.Store.emit(this.Events.OFFCANVAS_OPENED);
    }

    /**
     * Hide currently opened offCanvas element
     */
    close (element) {
        this.element.removeClass(this.options.styleClasses.activeClass);
        $(this.element.data('container')).removeClass(this.options.styleClasses.activeClass).attr('aria-hidden', 'true');
        document.querySelector('body').classList.remove(this.options.styleClasses.offcanvasOpenedClass);
        if (this.options.moveBodyOnOpen) {
            this.selectors.offCanvasWrapper.classList.remove(this.options.styleClasses.rightDirectionClass, this.options.styleClasses.leftDirectionClass);
        }
        this.Store.emit(this.Events.OFFCANVAS_CLOSED);
    }

    opened (data) {
    }

    closed (data) {
    }

    initialized (data) {
    }
}
