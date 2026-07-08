define(['module', 'exports', 'jquery'], function (module, exports, _jquery) {
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

    var Amount = function () {
        function Amount(perPageCount, currentCount, totalCount) {
            var block = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : (0, _jquery2.default)('#toolbar-amount');

            _classCallCheck(this, Amount);

            this.perPageCount = parseInt(perPageCount, 10);
            this.currentCount = parseInt(currentCount, 10);
            this.totalCount = parseInt(totalCount, 10);
            this.block = block;
            this._render();
        }

        _createClass(Amount, [{
            key: '_render',
            value: function _render() {
                this.block.html(this._updateAmount());
            }
        }, {
            key: '_updateAmount',
            value: function _updateAmount() {
                if (this._getLastNumber() > 1) {
                    return 'Showing: 1 - ' + this.currentCount + ' of ' + this.totalCount;
                } else if (this.totalCount === 1) {
                    return this.totalCount + ' Item';
                } else {
                    return this.totalCount + ' Items';
                }
            }
        }, {
            key: '_getLastNumber',
            value: function _getLastNumber() {
                if (this.totalCount === 0) {
                    return 1;
                } else if (this.perPageCount) {
                    return Math.ceil(this.totalCount / this.perPageCount);
                } else {
                    return 1;
                }
            }
        }]);

        return Amount;
    }();

    exports.default = Amount;
    module.exports = exports['default'];
});
