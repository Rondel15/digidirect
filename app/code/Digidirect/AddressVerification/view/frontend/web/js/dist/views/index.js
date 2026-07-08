define(['module', 'exports', 'jquery', './../common/store'], function (module, exports, _jquery, _store) {
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

    var _slicedToArray = function () {
        function sliceIterator(arr, i) {
            var _arr = [];
            var _n = true;
            var _d = false;
            var _e = undefined;

            try {
                for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) {
                    _arr.push(_s.value);

                    if (i && _arr.length === i) break;
                }
            } catch (err) {
                _d = true;
                _e = err;
            } finally {
                try {
                    if (!_n && _i["return"]) _i["return"]();
                } finally {
                    if (_d) throw _e;
                }
            }

            return _arr;
        }

        return function (arr, i) {
            if (Array.isArray(arr)) {
                return arr;
            } else if (Symbol.iterator in Object(arr)) {
                return sliceIterator(arr, i);
            } else {
                throw new TypeError("Invalid attempt to destructure non-iterable instance");
            }
        };
    }();

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

    var Autocomplete = function () {
        /**
         * Initializing of view-model
         * @param options
         */

        function Autocomplete(options) {
            _classCallCheck(this, Autocomplete);

            this.options = Object.assign({}, this.options, options);

            this.selectors = options.selectors;
            this.config = options.config;
            this.inputField = options.inputField;


            // list of form field nodes
            this.nodes = {};
            for (var selector in this.selectors) {
                this.nodes[this.selectors[selector]] = (0, _jquery2.default)('[name="' + this.selectors[selector] + '"]').filter(':visible').first()[0];
            }
            // initializing watchers
            this.watchers();

            this._loadGoogleApi(window.digidirectGoogleMapsUrl || '//maps.googleapis.com/maps/api/js?loading=async&key=' + options.gplaces_config.api_key + '&libraries=places');

            // Initialization value
            this.isFormComplete = false;
        }

        /**
         * Loading of google places library
         * @private
         */


        _createClass(Autocomplete, [{
            key: '_loadGoogleApi',
            value: function _loadGoogleApi(mapUrl) {
                var _this = this;

                try {
                    require([mapUrl], function () {
                        _this.initAutocomplete(_this.inputField, _this.config, _this.selectors);
                    });
                } catch (e) {
                    console.warn('Google Places Library hasn\'t been downloaded', e);
                }
            }
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this2 = this;

                _store.Store.on(_store.Events.PLACE_SELECT_PENDING, function () {
                    _this2._getPlace();
                });
                _store.Store.on(_store.Events.FORM_CHANGE_SUCCESS, function (data) {
                    return _this2.success(data);
                });
                _store.Store.on(_store.Events.ERROR, function (data) {
                    return _this2.error(data);
                });
                _store.Store.on(_store.Events.PLACES_DOWNLOAD_ERROR, function (data) {
                    return _this2.error(data);
                });
                _store.Store.on(_store.Events.GOOGLE_MAPS_AUTHENTICATION_ERROR, function (data) {
                    return _this2.gmError(data);
                });

                // watches for authentication errors from Google Places API
                window.gm_authFailure = function () {
                    _store.Store.emit(_store.Events.GOOGLE_MAPS_AUTHENTICATION_ERROR);
                };
            }
        }, {
            key: 'initAutocomplete',
            value: function initAutocomplete(selector, componentForm, formNames) {
                var _this3 = this;

                var self = this,
                    service = new google.maps.places.AutocompleteService(),
                    isAutocompleteAttribute = 'digidirect-autocomplete',
                    node = (0, _jquery2.default)('[name="' + selector + '"]:not([' + isAutocompleteAttribute + '])').filter(':visible').first();

                if (node.length == 0) return;

                // initialize autocomplete with node[0]
                this.autocomplete = new google.maps.places.Autocomplete(node[0], { types: ['geocode'] });
                (0, _jquery2.default)(node[0]).attr(isAutocompleteAttribute, 'on');

                // set restricted country if selected
                node.on('focus', function () {
                    _this3._setRestriction();
                });

                this.autocomplete.addListener('place_changed', function () {
                    self._getPlace();
                    self.fillInAddress([_this3.config, _this3.selectors]);
                    self.isFormComplete = true;
                });

                node.on('input', function () {
                    var input = this;
                    if (input.value) {
                        service.getPlacePredictions({ input: input.value }, function (data, status) {
                            if (status === 'ZERO_RESULTS' && !self.isFormComplete) {
                                // reset form
                                self.fieldsChanging();
                            }
                            if (status !== 'OK' && status !== 'ZERO_RESULTS') {
                                _store.Store.emit(_store.Events.PLACES_DOWNLOAD_ERROR);
                            }
                        });
                    }
                });

                // set empty placeholder for init field
                node.attr('placeholder', 'Street');

                // check if we have preselected and restrict in this case
                if (!this.autocomplete.componentRestrictions) {
                    this._setRestriction();
                }
            }
        }, {
            key: 'fieldsChanging',
            value: function fieldsChanging() {
                var components = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];

                var checked = {},
                    isEmpty = void 0,
                    stateValue = void 0,
                    nodeArray = Object.keys(this.nodes),
                    subpremise = void 0;

                var _ref = Object.keys(components).length ? [components, false] : [nodeArray, true];

                var _ref2 = _slicedToArray(_ref, 2);

                components = _ref2[0];
                isEmpty = _ref2[1];


                for (var i = 0, length = Object.keys(components).length; i < length; i++) {
                    var type = void 0,
                        val = void 0,
                        node = void 0;

                    if (!isEmpty) {
                        type = components[i].types[0];
                        val = components[i][this.config[type]] ? components[i][this.config[type]] : '';
                        node = this.nodes[this.selectors[type]];
                        if (type === 'subpremise') {
                            subpremise = val;
                        }
                        if (subpremise && type === 'street_number') {
                            val = subpremise + '/' + val;
                        }
                        if (node) {
                            if (type === 'administrative_area_level_1') {
                                stateValue = val;
                            }
                            if (node.nodeName.toLowerCase() === 'select') {
                                if (type === 'country') {
                                    (0, _jquery2.default)(node).val(val);
                                }
                            } else {
                                if (checked[this.selectors[type]]) {
                                    node.value += ' ' + val;
                                } else {
                                    node.value = val;
                                }
                            }
                            checked[this.selectors[type]] = true;
                        }
                    } else {
                        node = this.nodes[nodeArray[i]];
                        if (node) {
                            if (node.nodeName.toLowerCase() === 'select') {
                                node.options[0].selected = 'selected';
                            } else {
                                if (this.inputField !== node.name) {
                                    node.value = '';
                                }
                            }
                        }
                    }
                    (0, _jquery2.default)(node).trigger('change');
                }
                if (stateValue) {
                    return stateValue;
                }
            }
        }, {
            key: 'fillInAddress',
            value: function fillInAddress(_ref3) {
                var _ref4 = _slicedToArray(_ref3, 2),
                    componentForm = _ref4[0],
                    formNames = _ref4[1];

                var checked = {},
                    self = this,
                    stateValue = void 0;

                stateValue = self.fieldsChanging(self.place.address_components);

                if (stateValue) {
                    var node = self.nodes[this.selectors['administrative_area_level_1']];
                    if (node) {
                        // choose visible field on default address account and checkout pages
                        if ((0, _jquery2.default)(node).css('display') === 'none' || (0, _jquery2.default)(node).closest('.field').css('display') === 'none') {
                            (0, _jquery2.default)(self.nodes[this.selectors['administrative_area_level_alter']]).val(stateValue).trigger('change');
                            checked.region = true;
                        } else {
                            var options = node.options;
                            if (options) {
                                for (var j = 0, length = options.length; j < length; j++) {
                                    if (options[j].innerHTML.toLowerCase() === stateValue.toLowerCase()) {
                                        options[j].selected = 'selected';
                                    }
                                }
                            }
                        }
                        (0, _jquery2.default)(node).trigger('change');
                    }
                }
                _store.Store.emit(_store.Events.FORM_CHANGE_SUCCESS);
            }
        }, {
            key: '_getPlace',
            value: function _getPlace() {
                try {
                    this.place = this.autocomplete.getPlace();
                } catch (e) {
                    _store.Store.emit(_store.Events.PLACE_SELECT_ERROR);
                }
            }
        }, {
            key: '_setRestriction',
            value: function _setRestriction() {
                var country = (0, _jquery2.default)(this.nodes[this.selectors['country']]).val();
                if (country === 'all' || country === '') {
                    this.autocomplete.setComponentRestrictions({ country: [] });
                } else if (country) {
                    this.autocomplete.setComponentRestrictions({ country: country.toLowerCase() });
                } else {
                    delete this.autocomplete.componentRestrictions;
                }
            }
        }, {
            key: 'error',
            value: function error() {}
        }, {
            key: 'success',
            value: function success() {

            }
        }, {
            key: 'gmError',
            value: function gmError() {
                var node = (0, _jquery2.default)('[name="' + this.inputField + '"]');
                node.attr({ placeholder: '', disabled: false }).css('background-image', '');
                google.maps.event.clearInstanceListeners(node[0]);
            }
        }]);

        return Autocomplete;
    }();

    exports.default = Autocomplete;
    module.exports = exports['default'];
});
