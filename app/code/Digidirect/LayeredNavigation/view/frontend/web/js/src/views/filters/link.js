import $ from 'jquery';
import View from './../index';

export default class Link {
    constructor (options) {
        this.options = options;
        this.watchers(this.options);
    }

    watchers (options) {
        var $link = $(options.linkElement[0]),
            url = $link.attr('href');

        $link.on('click', function (e) {
            e.preventDefault();

            if (options.resetParameters || (!options.triggerApplyButton && !View.applyMode(options))) {
                View.sendRequest(options, {
                    url: url,
                    type: 'link'
                });
            }
        });
    }
}
