define(['module', 'exports', 'jquery', 'knockout', './../../common/store', 'text!Digidirect_InfiniteScroll/template/button.html', './item-click'], function (module, exports, _jquery, _knockout, _store, _button, _itemClick) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _knockout2 = _interopRequireDefault(_knockout);

    var _button2 = _interopRequireDefault(_button);

    var _itemClick2 = _interopRequireDefault(_itemClick);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _classCallCheck(instance, Constructor) {
        if (!(instance instanceof Constructor)) {
            throw new TypeError("Cannot call a class as a function");
        }
    }

    var _createClass = function () {
        function defineProperties(target, props) {
            for (var i = 0; i < props.length; i++) {
                var descriptor = props[i];
                descriptor.enumerable = descriptor.enumerable || false;
                descriptor.configurable = true;
                if ("value" in descriptor) descriptor.writable = true;
                Object.defineProperty(target, descriptor.key, descriptor);
            }
        }

        return function (Constructor, protoProps, staticProps) {
            if (protoProps) defineProperties(Constructor.prototype, protoProps);
            if (staticProps) defineProperties(Constructor, staticProps);
            return Constructor;
        };
    }();

    var Action = function () {
        function Action(options, view) {
            _classCallCheck(this, Action);

            var self = this,
                ViewModelButton;
            this.options = {
                buttonTemplate: _button2.default
            };
            this.options = Object.assign({}, this.options, options);

            if (this.options.scrollToLastViewedItem) {
                new _itemClick2.default(options);
            }

            ViewModelButton = function ViewModelButton(state) {
                this.buttonState = _knockout2.default.observable(state);
                this.onClick = function () {
                    _store.Store.emit(_store.Events.DATA_FETCH_START, view.options.nextUrl);
                };
            };

            this.vmb = new ViewModelButton(self.options.buttonContent);

            self._renderButton(this.options);
        }

        /**
         * Render template of button
         * @private
         */


        _createClass(Action, [{
            key: '_renderButton',
            value: function _renderButton(options) {
                var buttonHtml = (0, _jquery2.default)(options.buttonArea)[0];

                if (options.nextUrl && (0, _jquery2.default)(options.buttonArea).length) {
                    if (!options.buttonPrepend) {
                        (0, _jquery2.default)(options.buttonTemplate).appendTo(options.buttonArea);
                    } else {
                        (0, _jquery2.default)(options.buttonTemplate).prependTo(options.buttonArea);
                    }
                    _knockout2.default.applyBindings(this.vmb, buttonHtml);
                }
            }
        }]);

        return Action;
    }();

    exports.default = Action;
    module.exports = exports['default'];
});
