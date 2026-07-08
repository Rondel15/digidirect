define(['module', 'exports', 'jquery', 'digidirectUtils', 'jquery/ui', 'domReady!'], function (module, exports, _jquery, _digidirectUtils) {
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

    _jquery2.default.widget('digidirect.truncateDotdotdot', {
        version: '0.0.2',
        options: {
            dotdotdot: '...',
            step: 3,
            dataAttr: 'data-truncate-default-text',
            helperClass: '_truncate-clearfix'
        },
        _create: function _create() {
            var _this = this;

            this.$element = (0, _jquery2.default)(this.element);
            setTimeout(function () {
                _this.truncate();
            }, 0);
        },
        _loadAddOn: function _loadAddOn() {
            var options = this.options,
                store = this.store,
                events = this.events;
            (0, _digidirectUtils.loadAddOn)({ options: options, store: store, events: events }, this, 'Truncate Dotdotdot');
            return this;
        },
        _setOptions: function _setOptions(key, value) {
            this._super("_setOption", key, value);
        },
        destroy: function destroy() {
            _jquery2.default.Widget.prototype.destroy.call(this);
        },
        truncate: function truncate() {
            var $element = this.$element,
                $parent = $element.parent(),
                $clone = $element.clone(),
                defaultText = $element.attr(this.options.dataAttr) || $element.text(),
                text = $element.text(),
                height = $element.height(),
                parentHeight = $parent.height();

            if (height <= parentHeight) {
                return;
            }
            $element.addClass(this.options.helperClass);
            $clone.css({
                visibility: 'hidden',
                width: $parent.width() + 'px'
            });
            $element.after($clone);

            var length = text.length - this.options.step;
            for (; length >= 0 && $clone.height() > parentHeight; length -= this.options.step) {
                $clone.text(text.substring(0, length) + this.options.dotdotdot);
            }

            $element.text($clone.text()).attr(this.options.dataAttr, defaultText).removeClass(this.options.helperClass);
            $clone.remove();
        }
    });

    exports.default = _jquery2.default.digidirect.truncateDotdotdot;
    module.exports = exports['default'];
});
