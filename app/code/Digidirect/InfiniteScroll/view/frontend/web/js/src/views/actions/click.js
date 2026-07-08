import $ from 'jquery';
import ko from 'knockout';
import {Store, Events} from './../../common/store';
import Button from 'text!Digidirect_InfiniteScroll/template/button.html';
import ItemClick from './item-click';

export default class Action {
    constructor (options, view) {
        var self = this,
            ViewModelButton;
        this.options = {
            buttonTemplate: Button
        };
        this.options = Object.assign({}, this.options, options);

        if (this.options.scrollToLastViewedItem) {
            new ItemClick(options);
        }

        ViewModelButton = function (state) {
            this.buttonState = ko.observable(state);
            this.onClick = () => {
                Store.emit(Events.DATA_FETCH_START, view.options.nextUrl);
            };
        };

        this.vmb = new ViewModelButton(self.options.buttonContent);

        self._renderButton(this.options);
    }

    /**
     * Render template of button
     * @private
     */
    _renderButton (options) {
        let buttonHtml = $(options.buttonArea)[0];

        if (options.nextUrl && $(options.buttonArea).length) {
            if (!options.buttonPrepend) {
                $(options.buttonTemplate).appendTo(options.buttonArea);
            } else {
                $(options.buttonTemplate).prependTo(options.buttonArea);
            }
            ko.applyBindings(this.vmb, buttonHtml);
        }
    }
}
