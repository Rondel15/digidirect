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

    var AddOn = function () {
        function AddOn(options) {
            var _this = this;

            _classCallCheck(this, AddOn);

            _digidirectStoreCatalog.GlobalStore.on(_digidirectStoreCatalog.GlobalEvents.PRODUCT_COLLECTION_UPDATED, function (data) {
                if (data.page_params.nextUrl) {
                    data.page_params.nextUrl = data.page_params.nextUrl + '&_is=' + options.perPageCount;
                    if (!(data.page_params.nextUrl.indexOf('product_list_dir') !== -1) && options.nextUrl.indexOf('product_list_dir') !== -1) {
                        var direction = _this.getParameterByName('product_list_dir', options.nextUrl);
                        data.page_params.nextUrl = data.page_params.nextUrl + '&product_list_dir=' + direction;
                    }
                }
                _this.options = Object.assign({}, options, data.page_params);
                _store.Store.emit(_store.Events.RELOAD, _this.options);
            });
        }

        _createClass(AddOn, [{
            key: 'getParameterByName',
            value: function getParameterByName(name, url) {
                if (!url) url = window.location.href;
                name = name.replace(/[\[\]]/g, '\\$&');
                var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
                    results = regex.exec(url);
                if (!results) return null;
                if (!results[2]) return '';
                return decodeURIComponent(results[2].replace(/\+/g, ' '));
            }
        }]);

        return AddOn;
    }();

    exports.default = AddOn;
    module.exports = exports['default'];
});
