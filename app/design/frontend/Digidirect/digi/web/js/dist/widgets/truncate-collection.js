define(['module', 'exports', 'jquery', 'digidirectUtils', './truncate-collection/common/store', 'jquery/ui', 'truncateDotdotdot', 'domReady!'], function (module, exports, _jquery, _digidirectUtils, _store) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    _jquery2.default.widget('digidirect.truncateCollection', {
        version: '0.0.1',
        options: {
            selector: '.product-item-link',
            parsedSelector: '[data-truncate-default-text]',
            childOptions: {}
        },
        _create: function _create() {
            var _this = this;

            this.store = _store.Store;
            this.events = _store.Events;
            this.$element = (0, _jquery2.default)(this.element);
            this._loadAddOn()._bindEventsListener();
            //pull out of the stream and put it at the end of the queue
            setTimeout(function () {
                _this.truncateRawСollection();
            }, 0);
        },
        _bindEventsListener: function _bindEventsListener() {
            var _this2 = this;

            this.store.on(this.events.TRUNCATE_RAW_COLLECTION, function () {
                //pull out of the stream and put it at the end of the queue
                setTimeout(function () {
                    _this2.truncateRawСollection();
                }, 0);
            });

            return this;
        },
        _loadAddOn: function _loadAddOn() {
            var options = this.options,
                store = this.store,
                events = this.events;
            (0, _digidirectUtils.loadAddOn)({ options: options, store: store, events: events }, this, 'Truncate Collection');
            return this;
        },
        _setOptions: function _setOptions(key, value) {
            this._super("_setOption", key, value);
        },
        destroy: function destroy() {
            _jquery2.default.Widget.prototype.destroy.call(this);
        },
        getRawСollection: function getRawOllection() {
            return (0, _jquery2.default)(this.element).find(this.options.selector).not(':digidirect-truncateDotdotdot');
        },
        truncateRawСollection: function truncateRawOllection() {
            var _this3 = this;

            this.getRawСollection().each(function (index, element) {
                (0, _jquery2.default)(element).truncateDotdotdot(_this3.options.childOptions);
            });

            return this;
        }
    });

    exports.default = _jquery2.default.digidirect.truncateCollection;
    module.exports = exports['default'];
});
