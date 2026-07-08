define(['module', 'exports', 'digidirectStoreCatalog', './../../common/store'], function (module, exports, _digidirectStoreCatalog, _store) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var AddOn = function AddOn() {
        _classCallCheck(this, AddOn);

        _store.Store.on(_store.Events.DATA_FETCH_SUCCESS, function (data) {
            _digidirectStoreCatalog.GlobalStore.emit(_digidirectStoreCatalog.GlobalEvents.PRODUCT_COLLECTION_UPDATED, data);
        });

        _store.Store.on(_store.Events.DATA_FETCH_START, function (data, options) {
            _digidirectStoreCatalog.GlobalStore.emit(_digidirectStoreCatalog.GlobalEvents.PRODUCT_COLLECTION_UPDATE_START, data, options);
        });
    };

    exports.default = AddOn;
    module.exports = exports['default'];
});
