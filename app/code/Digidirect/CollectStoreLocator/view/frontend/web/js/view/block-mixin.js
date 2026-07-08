define([
    'ko',
    'jquery'
], function (ko, $) {
    'use strict';

    return function (target) {
        return target.extend({
            defaults: {
                entityName: 'abstract_entity_store',
                collectLocatorBlock: '[data-role="collect-locator"]'
            },
            isLocatorLoading: ko.observable(true),
            showFormPopUp: function (data, e) {
                this._super(data, e);

                if (this.isLocatorLoading()) {
                    this.getLocatorBlock();
                }
            },
            getLocatorBlock: function () {
                console.log("getLocatorBlock() called!");
                
                var self = this;

                $.ajax({
                    method: 'POST',
                    url: window.checkoutConfig.quoteData.locator_block_url,
                    success: function (data) {
                        self.onSuccessLoadLocatorBlock(data);
                    },
                    error: function () {
                        self.onErrorLoadLocatorBlock();
                    }
                });
            },
            onSuccessLoadLocatorBlock: function (data) {
                console.log("onSuccessLoadLocatorBlock called!");
                var $container = $(this.collectLocatorBlock, this.popUpForm.element);

                $container.html(data.output).trigger('contentUpdated');
                $container.find('[data-bind^="scope"]').applyBindings();
                this.isLocatorLoading(false);
            },
            onErrorLoadLocatorBlock: function () {
                console.log("onErrorLoadLocatorBlock called!");
                console.warn('Locator hasn\'t been loaded');
            },
            submitLocatorStore: function (item) {
                this.applyCollectPlaceToAllItems(item.entity_id, this.entityName);
            }
        });
    };
});
