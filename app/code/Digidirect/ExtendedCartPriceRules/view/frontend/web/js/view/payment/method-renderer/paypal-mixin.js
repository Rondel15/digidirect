define(function () {
    'use strict';

    var mixin = {
        isActive: function () {
            if (this.isRemoveItemRuleApplied()) {
                var active = this.getCode() === this.isChecked();
                this.active(false);
                this.isEnabledPaymentButton(false);
                return active;
            }
            return this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});
