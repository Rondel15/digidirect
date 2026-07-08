import $ from 'jquery';
import {Store, Events} from './../common/store';

export default class View {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        const ACTIONS_FOLDER = 'Digidirect_LayeredNavigation/js/dist/views/actions/';

        this.loadActions(ACTIONS_FOLDER);

        this.watchers();

        this.checkFilterRemember();
    }
    /**
     * Ajax update or redirect
     * @param options
     * @param data
     */
    static sendRequest (options, data) {
        if (options.enableAjax) {
            const AJAX_PARAM = 'ajax_navigation=true';
            if (data.url.indexOf(AJAX_PARAM) === -1) {
                if (data.url.indexOf('?') !== -1) {
                    data.url += '&' + AJAX_PARAM;
                } else {
                    data.url += '?' + AJAX_PARAM;
                }
            }
            Store.emit(Events.DATA_FETCH_START, data, options);
        } else {
            window.location.href = data.url.replace(/\?$/, '');
        }
    }

    /**
     * Watchers
     */
    watchers () {
        Store.on(Events.DATA_FETCH_PROGRESS, () => this.progress());
        Store.on(Events.DATA_FETCH_SUCCESS, (data) => this.success(data));
        Store.on(Events.ERROR, (data) => this.error(data));
    }
    /**
     * Render content
     * @param data
     * @private
     */
    _renderData (data) {
        $.each(data, (index, block) => {
            $(block.selector).each((index, selector) => {
                $(selector).html(block.block);
                $(selector).trigger('contentUpdated');
            });
        });
    }

    loadActions (fileLocation) {
        if (this.options.triggerApplyButton || this.options.triggerApplyMode) {
            require(['./' + fileLocation + 'apply'], (Action) => {
                new Action(this.options);
            });
        }

        if (this.options.enableAjax) {
            require(['./' + fileLocation + 'toolbar'], (Toolbar) => {
                new Toolbar(this.options);
            });
        }
    }

    isFilterRememberEnabled () {
        return this.options.enableFilterRemember && !$(this.options.selectors.blockFilters).not(this.options.filterRememberDataAttr).length;
    }

    checkFilterRemember () {
        if (this.isFilterRememberEnabled()) {
            Store.emit(Events.DATA_FETCH_START, {
                url: window.location.href,
                isFilterRemember: true
            });
        }
    }

    /**
     * If filterRemember functionality enabled that Apply Action hidden by default
     * Show Apply Action
     */
    showApplyAction () {
        let $applyAction = $(this.options.selectors.applyButton);

        if (this.options.enableFilterRemember && $applyAction.hasClass('-hide')) {
            $applyAction.removeClass('-hide');
        }
    }

    /**
     * "Apply" logic will be provided for devices with the width of the screen less than specified value
     * @returns {boolean}
     */
    static applyMode (options) {
        if (options.triggerApplyMode && options.applyModeBreakpoint && window.matchMedia(options.applyModeBreakpoint).matches) {
            return true;
        }
        return false;
    }

    progress () {
    }

    success (data) {
        this._renderData(data);
        this.showApplyAction();
    }

    error () {
    }
}
