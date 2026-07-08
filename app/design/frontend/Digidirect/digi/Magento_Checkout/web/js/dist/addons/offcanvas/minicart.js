define(['module', 'exports', 'digidirectStoreCheckout'], function (module, exports, _digidirectStoreCheckout) {
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
        this.element = options.triggerElementSelector.first();
        _digidirectStoreCheckout.Store.on(_digidirectStoreCheckout.Events.MINICART_TOGGLE, function (data, state) {
            _this.Store.emit(_this.Events.OFFCANVAS_TOOGLE, data || _this.element, state || _this.Store.currentState);
        });
        _digidirectStoreCheckout.Store.on(_digidirectStoreCheckout.Events.MINICART_OPEN, function (data) {
            _this.Store.emit(_this.Events.OFFCANVAS_OPEN, data || _this.element);
        });
        _digidirectStoreCheckout.Store.on(_digidirectStoreCheckout.Events.MINICART_CLOSE, function (data) {
            _this.Store.emit(_this.Events.OFFCANVAS_CLOSE, data || _this.element);
        });
    };

    exports.default = AddOn;
    module.exports = exports['default'];
});
