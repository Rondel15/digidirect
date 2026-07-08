define([
    'jquery',
    'mage/template',
    'jquery/ui',
    'Magento_Bundle/js/price-bundle'
], function ($, mageTemplate) {
    'use strict';

    return function (target) {
        $.widget('mage.productSummary', target, {
            _renderSummaryBox: function (event, data) {
                this._super(event, data);
                var $title = $('#bundleSummary').find('.subtitle > .text');

                if (this.cache.currentElementCount) {
                    $title.attr('data-count', this.cache.currentElementCount);
                } else {
                    $title.attr('data-count', '');
                }
            },
            _renderOptionRow: function (key, optionIndex) {
                var template,
                    $option,
                    imgSrc;

                template = this.element.closest(this.options.summaryContainer).find(this.options.templates.optionBlock).html();
                $option = $('#bundle-option-' + this.cache.currentKey + '-' + optionIndex);

                if ($option.length) {
                    imgSrc = $option.closest('.bundle-item').find('img').attr('src');
                } else {
                    imgSrc = $('.bundle-option-' + this.cache.currentKey + '[value="' + optionIndex + '"]').closest('.bundle-item').find('img').attr('src');
                }

                template = mageTemplate($.trim(template), {
                    data: {
                        _quantity_: this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex].qty,
                        _label_: this.cache.currentElement.options[this.cache.currentKey].selections[optionIndex].name,
                        _img_src_: imgSrc
                    }
                });
                this.cache.summaryContainer.find(this.options.optionSelector).append(template);
            }
        });

        return $.mage.productSummary;
    }
});
