import $ from 'jquery';
import {Store, Events} from './../common/store';

export default class View {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        this.watchers();

        if (this.options.rememberScrollState) {
            this._restoreState();
        }

        this._loadAction(this.options);
    }
    _loadAction (options) {
        const ACTIONS_FOLDER = 'Digidirect_InfiniteScroll/js/dist/views/actions/';
        if ((options.nextUrl && options.action) || this.options.rememberScrollState) {
            require(['./' + ACTIONS_FOLDER + options.action], (Action) => {
                this.actionData = new Action(options, this);
            });
        }
    }

    /**
     * Restore previously saved scroll state
     * @private
     */
    _restoreState () {
        var state = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey));

        if (state && window.location.href === state.location && window.localStorage.getItem(this.options.itemUrlKey)) {
            $(this.options.itemsContainerSelector).append(state.content).trigger('contentUpdated');
            this.options.currentCount = state.currentCount;
            this.options.totalCount = state.totalCount;
            this.options.nextUrl = state.nextUrl;
        } else {
            window.localStorage.removeItem(this.options.scrollStateKey);
        }
    }

    /**
     * Save infinite scroll state and HTML of loaded items
     * @param data
     * @private
     */
    _saveState (data) {
        var currentState = JSON.parse(window.localStorage.getItem(this.options.scrollStateKey)),
            newState = {
                location: window.location.href,
                nextUrl: data.url,
                totalCount: data.totalCount,
                currentCount: data.currentCount,
                perPageCount: data.perPageCount
            },
            content = '';

        if (currentState && window.location.href === currentState.location) {
            content = currentState.content;
        }

        newState.content = content + data.content;

        window.localStorage.setItem(this.options.scrollStateKey, JSON.stringify(newState));
    }
    /**
     * Watchers
     */
    watchers () {
        Store.on(Events.DATA_FETCH_PROGRESS, () => this.progress());
        Store.on(Events.DATA_FETCH_SUCCESS, (data) => this.success(data));
        Store.on(Events.DATA_FETCH_FINISH, (data) => this.finish(data));
        Store.on(Events.RELOAD, (options) => this.reload(options));
        Store.on(Events.ERROR, (data) => this.error(data));
    }
    unwatch () {
        $(this.options.scrollContainer).off('scroll');

        Store.off(Events.DATA_FETCH_PROGRESS, '*');
        Store.off(Events.DATA_FETCH_SUCCESS, '*');
        Store.off(Events.DATA_FETCH_FINISH, '*');
        Store.off(Events.RELOAD, '*');
        Store.off(Events.ERROR, '*');
    }
    /**
     * Render content
     * @param data
     * @private
     */
    _renderData (data) {
        $(this.options.itemsContainerSelector).append(data.content);
    }

    progress () {
    }

    success (data) {
        this._renderData(data);
        this.options.nextUrl = data.url;
        $(this.options.itemsContainerSelector).trigger('contentUpdated');

        if (this.options.rememberScrollState) {
            this._saveState(data);
        }
    }

    finish () {
    }

    reload (options) {
        this.unwatch();

        new View(options);
    }

    error () {
    }
}
