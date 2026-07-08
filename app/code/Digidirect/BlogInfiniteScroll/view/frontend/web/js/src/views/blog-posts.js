import $ from 'jquery';
import View from 'infinitescrollView';

export default class ViewCustom extends View {
    constructor (options) {
        super(options);
        this.postsWrapper = $('.blog-list');
        this.infiniteWrapper = $('.blog-infinite');
    }

    _renderData (data) {
        let $data = $(data.content.trim());
        $(this.options.itemsContainerSelector).append($data.html());
    }

    progress () {
        let self = this;
        this.postsWrapper.loader({'icon': self.options.loaderIcon, 'texts': {'loaderText': self.options.loaderText}}).trigger('processStart');
    }

    success (data) {
        super.success(data);
        this.postsWrapper.loader().trigger('processStop');
    }

    finish () {
        this.infiniteWrapper.find('.infinitescroll-button').remove();
    }

    error () {
        this.postsWrapper.loader().trigger('processStop');
    }
}
