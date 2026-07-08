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

    var Model = function () {
        function Model() {
            _classCallCheck(this, Model);

            this.fetchData = this.getData;
        }

        _createClass(Model, [{
            key: 'getData',
            value: function getData(url) {
                if (typeof url !== 'string') {
                    return false;
                }
                return (0, _digidirectUtils.callFetch)(url, this.setOptions(), undefined, this.getFetchResponseHtml);
            }
        }, {
            key: 'getFetchResponseHtml',
            value: function getFetchResponseHtml(response) {
                return response.text();
            }
        }, {
            key: 'setOptions',
            value: function setOptions() {
                return {
                    method: 'GET',
                    headers: new Headers({
                        'Accept': 'text/html'
                    })
                };
            }
        }]);

        return Model;
    }();

    exports.default = Model;
    module.exports = exports['default'];
});
