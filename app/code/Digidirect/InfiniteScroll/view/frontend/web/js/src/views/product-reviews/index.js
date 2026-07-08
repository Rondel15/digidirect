import $ from 'jquery';
import View from './../index';

export default class ViewCustom extends View {
    constructor (options) {
        super(options);
        this.reviewsWrapper = $('#customer-reviews .block-content');
    }

    _renderData (data) {
        let $data = $(data.content);
        $(this.options.itemsContainerSelector).find('.review-items').append($data.find('.review-items').html());
    }

    progress () {
        var self = this;
        this.reviewsWrapper.loader({'icon': self.options.loaderIcon, 'texts': {'loaderText': self.options.loaderText}}).trigger('processStart');
    }

    success (data) {
        super.success(data);
        this.reviewsWrapper.loader().trigger('processStop');
    }

    finish () {
        this.reviewsWrapper.find('.infinitescroll-button').remove();
    }
}
