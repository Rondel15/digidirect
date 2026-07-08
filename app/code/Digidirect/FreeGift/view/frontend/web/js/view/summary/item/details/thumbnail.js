define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';

    var mixin = {
        updateImages: function (data) {
            for (var img in data) {
                this.imageData[data[img].item_id] = data[img].product_image;
            }
        },
        getSrc: function (item) {
            if (!this.imageData[item.item_id]) {
                customerData.reload(['cart'], false); // I think it should be invalidated (cart cache) via backend

                this.updateImages(customerData.get('cart')().items);
            }
            if (this.imageData[item.item_id]) {
                return this.imageData[item.item_id]['src'];
            }
            return null;
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
