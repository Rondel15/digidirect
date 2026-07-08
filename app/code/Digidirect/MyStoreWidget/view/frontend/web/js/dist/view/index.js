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

    var View = function () {
        function View(options) {
            _classCallCheck(this, View);

            this.options = Object.assign({}, this.options, options);
            this.componentForm = {
                locality: 'long_name',
                country: 'long_name',
                postal_code: 'short_name'
            };

            this.loadGoogleLibrary();
        }

        /**
         * Loads Google Library
         */


        _createClass(View, [{
            key: 'loadGoogleLibrary',
            value: function loadGoogleLibrary() {
                var _this = this;

                var googleMapsUrl = window.digidirectGoogleMapsUrl,
                    mapUrl = void 0;
                try {
                    if (googleMapsUrl !== undefined) {
                        mapUrl = googleMapsUrl;
                    } else {
                        mapUrl = '//maps.googleapis.com/maps/api/js?loading=async&key=' + this.options.googleAutoSuggestApiKey + '&libraries=places';
                    }
                    require([mapUrl], function () {
                        _this.init();
                    });
                } catch (e) {
                    console.warn('Google Places Library hasn\'t been downloaded', e);
                }
            }
        }, {
            key: 'init',
            value: function init() {
                var autocomplete = new google.maps.places.Autocomplete((0, _jquery2.default)(this.options.input)[0], { types: ['geocode'] });

                if (this.options.singleCountryData) {
                    autocomplete.setComponentRestrictions(this.options.singleCountryData);
                }

                this.bind(autocomplete);
            }
        }, {
            key: 'bind',
            value: function bind(autocomplete) {
                var _this2 = this;

                autocomplete.addListener('place_changed', function () {
                    return _this2.fillInAddress(autocomplete);
                });
                (0, _jquery2.default)(this.options.input).on('focus', function () {
                    return _this2.focus();
                });
                (0, _jquery2.default)(this.options.input).on('blur', function () {
                    return _this2.blur();
                });
                (0, _jquery2.default)(this.options.form).on('submit', function () {
                    return _this2.submit();
                });
            }
        }, {
            key: 'clearFormData',
            value: function clearFormData() {
                var prefix = this.options.googleInputPrefix;
                (0, _jquery2.default)(this.options.findStoreInput).val(0);
                for (var component in this.componentForm) {
                    (0, _jquery2.default)(prefix + component).val('');
                }
            }
        }, {
            key: 'fillInAddress',
            value: function fillInAddress(instanse) {
                var place = instanse.getPlace(),
                    prefix = this.options.googleInputPrefix;

                this.clearFormData();

                // Get each component of the address from the place details
                // and fill the corresponding field on the form.
                if (place.address_components) {
                    for (var i = 0; i < place.address_components.length; i++) {
                        var addressType = place.address_components[i].types[0];
                        if (this.componentForm[addressType]) {
                            var val = place.address_components[i][this.componentForm[addressType]];
                            (0, _jquery2.default)(prefix + addressType).val(val);
                        }
                    }
                    (0, _jquery2.default)(this.options.findStoreInput).val(1);
                    this.isSelectedStore = true;
                    this.checkSubmitForm();
                }
            }
        }, {
            key: 'geolocate',
            value: function geolocate() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(function (position) {
                        var geolocation = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        },
                            circle = new google.maps.Circle({
                            center: geolocation,
                            radius: position.coords.accuracy
                        });
                        this.autocomplete.setBounds(circle.getBounds());
                    });
                } else {
                    console.warn('The browser does not support Geolocation.');
                }
            }
        }, {
            key: 'focus',
            value: function focus() {
                var _this3 = this;

                this.isSelectedStore = false;
                this.isNeedToSendForm = false;

                if (this.options.isGeoLocationEnabled) {
                    (0, _jquery2.default)(this.options.input).on('focus', function () {
                        return _this3.focus;
                    });
                }
            }
        }, {
            key: 'blur',
            value: function blur() {
                this.isSelectedStore = true;
            }
        }, {
            key: 'submit',
            value: function submit() {
                var val = (0, _jquery2.default)(this.options.input).val();
                if (!this.isSelectedStore && val != '') {
                    this.isNeedToSendForm = true;
                    return false;
                }
            }
        }, {
            key: 'checkSubmitForm',
            value: function checkSubmitForm() {
                if (this.isNeedToSendForm) {
                    (0, _jquery2.default)(this.options.form).submit();
                }
            }
        }]);

        return View;
    }();

    exports.default = View;
    module.exports = exports['default'];
});
