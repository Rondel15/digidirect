define(['module', 'exports', 'digidirectUtils', 'eventPubSubExtended', './constants'], function (module, exports, _digidirectUtils, _eventPubSubExtended, _constants) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _eventPubSubExtended2 = _interopRequireDefault(_eventPubSubExtended);

    var Constants = _interopRequireWildcard(_constants);

    function _interopRequireWildcard(obj) {
        if (obj && obj.__esModule) {
            return obj;
        } else {
            var newObj = {};

            if (obj != null) {
                for (var key in obj) {
                    if (Object.prototype.hasOwnProperty.call(obj, key)) newObj[key] = obj[key];
                }
            }

            newObj.default = obj;
            return newObj;
        }
    }

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

    var Component = function () {
        function Component(options, element) {
            _classCallCheck(this, Component);

            this.options = options;
            this.element = element;
            this.store = new _eventPubSubExtended2.default(Constants);
            this.events = Constants;
            this.bind();
        }

        _createClass(Component, [{
            key: 'bind',
            value: function bind() {
                this._loadView();
                this._loadAddOn();
            }
        }, {
            key: '_loadView',
            value: function _loadView() {
                var options = this.options,
                    store = this.store,
                    events = this.events,
                    element = this.element;
                (0, _digidirectUtils.loadView)({ options: options, store: store, events: events, element: element }, this, './js/components/offcanvas/dist/views/index', 'OffCanvas');
            }
        }, {
            key: '_loadAddOn',
            value: function _loadAddOn() {
                var options = this.options,
                    store = this.store,
                    events = this.events;
                (0, _digidirectUtils.loadAddOn)({ options: options, store: store, events: events }, this, 'OffCanvas');
            }
        }]);

        return Component;
    }();

    exports.default = Component;
    module.exports = exports['default'];
});
