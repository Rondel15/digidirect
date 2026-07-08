import $ from 'jquery';
import View from './../index';

export default class Dropdown {
    constructor (options) {
        this.options = options;
        this.watchers(this.options);
    }

    watchers (options) {
        var $select = $(options.dropdownElement[0]),
            option;
        $select.on('change', () => {
            if (options.triggerApplyButton || View.applyMode(options)) {
                option = $select.find('option:selected');
                $select.find('option').removeClass('selected');
                option.addClass('swatch-option-link-layered selected');
                option.attr('href', option.attr('value'));
            } else {
                View.sendRequest(options, {
                    url: $select.val(),
                    type: 'dropdown'
                });
            }
        });
    }
}
