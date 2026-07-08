define(['module', 'exports', 'jquery', 'jquery/ui', 'domReady!'], function (module, exports, _jquery) {
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

    _jquery2.default.widget('digidirect.eventTransporter', {
        version: '0.0.1',
        options: {
            triggerEventList: 'click',
            invokedEvent: 'click',
            customerEventSelector: false
        },
        _create: function _create() {
            this.$element = (0, _jquery2.default)(this.element);

            this._bindEvents();
        },
        _bindEvents: function _bindEvents() {
            var _this = this;

            this.$element.on(this.options.triggerEventList, function (event) {
                event.preventDefault();
                (0, _jquery2.default)(_this.options.customerEventSelector).trigger(_this.options.invokedEvent);
            });
        },
        destroy: function destroy() {
            _jquery2.default.Widget.prototype.destroy.call(this);
        }
    });

    exports.default = _jquery2.default.digidirect.eventTransporter;
    module.exports = exports['default'];
});
