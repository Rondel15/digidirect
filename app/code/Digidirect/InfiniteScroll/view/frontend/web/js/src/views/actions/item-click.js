import $ from 'jquery';

export default class ItemClick {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);
        this.watchers(this.options);
        this.scrollToLastViewedItem();
    }

    watchers (options) {
        // save URL of clicked item
        $(options.itemsContainerSelector)
            .on('click', options.itemUrlSelector, function () {
                var $this = $(this),
                    itemUrl = $this.attr('href');
                if (itemUrl) {
                    window.localStorage.setItem(options.itemUrlKey, itemUrl);
                }
            });
    }

    /**
     * Scroll to last viewed item
     */
    scrollToLastViewedItem () {
        var itemUrl = window.localStorage.getItem(this.options.itemUrlKey),
            $container;

        if (itemUrl) {
            $container = this.getChildItemContainer(itemUrl);
            if ($container.length) {
                $container[0].scrollIntoView({behavior: 'smooth'});
            }
            window.localStorage.removeItem(this.options.itemUrlKey);
        }
    }

    /**
     * Get child item container by href attribute
     * @param href
     * @returns {object}
     */
    getChildItemContainer (href) {
        return $(this.options.itemsContainerSelector)
            .find('a[href="' + href + '"]')
            .closest(this.options.itemsContainerSelector + ' ' + this.options.itemSelector);
    }
}
