define(function () {
    'use strict';

    var mixin = {
        onExtendedValueChanged: function (newExportedValue) {
            if (!this.isCustomCheckoutField()) {
                this._super(newExportedValue);
            }
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});