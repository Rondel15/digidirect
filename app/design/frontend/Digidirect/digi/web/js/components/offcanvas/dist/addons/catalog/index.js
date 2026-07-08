define(['module', 'exports', 'digidirectStoreCatalog'], function (module, exports, _digidirectStoreCatalog) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    function _objectWithoutProperties(obj, keys) {
        var target = {};

        for (var i in obj) {
            if (keys.indexOf(i) >= 0) continue;
            if (!Object.prototype.hasOwnProperty.call(obj, i)) continue;
            target[i] = obj[i];
        }

        return target;
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var AddOn = function AddOn(_ref) {
        var _this = this;

        var options = _ref.options,
            _store = _objectWithoutProperties(_ref, ['options']);

        _classCallCheck(this, AddOn);

        this.Store = _store.store;
        this.Events = _store.events;
        _digidirectStoreCatalog.GlobalStore.on(_digidirectStoreCatalog.GlobalEvents.PRODUCT_COLLECTION_UPDATE_START, function (data) {
            _this.Store.emit(_this.Events.OFFCANVAS_CLOSE);
        });
    };

    exports.default = AddOn;
    module.exports = exports['default'];
});
