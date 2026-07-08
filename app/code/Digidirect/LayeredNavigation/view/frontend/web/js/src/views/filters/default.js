import $ from 'jquery';
import View from './../index';

export default class Default {
    constructor (options) {
        this.options = options;

        this.watchers(this.options);
    }

    watchers (options) {
        var element = options.element,
            checkbox = element.find('input'),
            multipleSelect = element.data('multiselect'),
            url;

        element.on('click', function (e) {
            if (e.target.nodeName.toLowerCase() !== 'input') {
                e.preventDefault();
                checkbox.prop('checked', !checkbox.prop('checked'));
            }

            if (element.find('.swatch-option.disabled').length) {
                return false;
            }

            if (!multipleSelect) {
                element.closest(options.selectors.filterContent).find('a').each(function () {
                    let $this = $(this);
                    if ($this.attr('href') !== element.attr('href')) {
                        $this.removeClass('selected');
                        $this.find('.swatch-option').removeClass('selected');
                        $this.find('input').prop('checked', false);
                    }
                });
            }

            if (element.hasClass('selected')) {
                element.removeClass('selected');
                element.find('.swatch-option').removeClass('selected');
            } else {
                element.addClass('selected');
                element.find('.swatch-option').addClass('selected');
            }

            if (!options.triggerApplyButton && !View.applyMode(options)) {
                url = element.attr('href');
                element.find('.swatch-option').trigger('mouseleave');
                View.sendRequest(options, {
                    url: url,
                    type: 'default'
                });
            }
        });
    }
}
