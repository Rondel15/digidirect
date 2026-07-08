define(['module', 'exports', 'digidirectUtils'], function (module, exports, _digidirectUtils) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

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
        function Component(options) {
            _classCallCheck(this, Component);

            this.options = options;
            this.bind(this.options);
        }

        _createClass(Component, [{
            key: 'bind',
            value: function bind(options) {
                (0, _digidirectUtils.loadView)(options, this, 'Digidirect_MyStoreWidget/js/dist/view/index', 'MyStoreWidget');
            }
        }]);

        return Component;
    }();

    exports.default = Component;
    module.exports = exports['default'];
});
