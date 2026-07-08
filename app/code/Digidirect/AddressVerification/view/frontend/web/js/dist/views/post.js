define(['module', 'exports', 'jquery', 'underscore', './../common/store'], function (module, exports, _jquery, _underscore, _store) {
    'use strict';

    Object.defineProperty(exports, "__esModule", {
        value: true
    });

    var _jquery2 = _interopRequireDefault(_jquery);

    var _underscore2 = _interopRequireDefault(_underscore);

    function _interopRequireDefault(obj) {
        return obj && obj.__esModule ? obj : {
            default: obj
        };
    }

    function _defineProperty(obj, key, value) {
        if (key in obj) {
            Object.defineProperty(obj, key, {
                value: value,
                enumerable: true,
                configurable: true,
                writable: true
            });
        } else {
            obj[key] = value;
        }

        return obj;
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

    var Post = function () {
        function Post(options) {
            _classCallCheck(this, Post);

            this.options = Object.assign({}, this.options, options);

            this.bind();
            this.watchers();
        }

        _createClass(Post, [{
            key: 'bind',
            value: function bind() {
                this.initializeAutocomplete();
                this.handleCountryChange();
                this.handleStateChange();
            }
        }, {
            key: 'watchers',
            value: function watchers() {
                var _this = this;

                _store.Store.on(_store.Events.POST_INITIALIZED, function ($element) {
                    return _this.initialized($element);
                });
                _store.Store.on(_store.Events.POST_DESTROYED, function ($element) {
                    return _this.destroyed($element);
                });
                _store.Store.on(_store.Events.POST_FETCH_DATA_PROGRESS, function (data) {
                    return _this.progress(data);
                });
                _store.Store.on(_store.Events.POST_FETCH_DATA_SUCCESS, function (data, response, value) {
                    return _this.success(data, response, value);
                });
                _store.Store.on(_store.Events.POST_FETCH_DATA_ERROR, function (data) {
                    return _this.error(data);
                });
            }
        }, {
            key: 'initializeAutocomplete',
            value: function initializeAutocomplete() {
                (0, _jquery2.default)(this.options.postcode).each(function (index) {
                    var $this = (0, _jquery2.default)(this);
                    $this.trigger('blur');
                });

                this.setPostcodeAutocomplete();
                this.setSuburbAutocomplete();
            }
        }, {
            key: 'destroyAutocomplete',
            value: function destroyAutocomplete($form) {
                var self = this,
                    $suburb = $form.find(self.options.suburb),
                    $postcode = $form.find(self.options.postcode);

                if ($suburb.data('ui-autocomplete') !== undefined) {
                    $suburb.autocomplete('destroy');
                    _store.Store.emit(_store.Events.POST_DESTROYED, $suburb);
                }
                if ($postcode.data('ui-autocomplete') !== undefined) {
                    $postcode.autocomplete('destroy');
                    _store.Store.emit(_store.Events.POST_DESTROYED, $postcode);
                }
            }
        }, {
            key: 'handleCountryChange',
            value: function handleCountryChange() {
                var self = this;

                (0, _jquery2.default)(document).on('change', self.options.country, function () {
                    var $form = (0, _jquery2.default)(this).closest('form');
                    if (self.isAllowedCountry($form)) {
                        self.initializeAutocomplete();
                    } else {
                        self.destroyAutocomplete($form);
                    }
                });
            }
        }, {
            key: 'handleStateChange',
            value: function handleStateChange() {
                var self = this;
                (0, _jquery2.default)(document).on('change', this.options.stateSelect, function () {
                    var $form = (0, _jquery2.default)(this).closest('form');
                    if (self.isAllowedCountry($form) && self.isApplicableAttribute($form.find(self.options.country).val(), 'region')) {
                        $form.find(self.options.postcode + ', ' + self.options.suburb).val('');
                    }
                });
            }
        }, {
            key: 'setSuburbAutocomplete',
            value: function setSuburbAutocomplete() {
                var self = this;

                (0, _jquery2.default)(document).on('focus', self.options.suburb, function () {
                    var $this = (0, _jquery2.default)(this),
                        $form = $this.closest('form');
                    self.assignAutocomplete($this, $form, 'suburb', 'postcode');
                });
            }
        }, {
            key: 'setPostcodeAutocomplete',
            value: function setPostcodeAutocomplete() {
                var self = this;

                (0, _jquery2.default)(document).on('focus', self.options.postcode, function () {
                    var $this = (0, _jquery2.default)(this),
                        $form = $this.closest('form');
                    self.assignAutocomplete($this, $form, 'postcode', 'suburb');
                });
            }
        }, {
            key: 'assignAutocomplete',
            value: function assignAutocomplete($element, $form, type, relativeElement) {
                var self = this,
                    autocompleteOptions = self.options.autocomplete;

                if ($element.data('ui-autocomplete') === undefined && self.isAllowedCountry($form)) {
                    $element.autocomplete({
                        appendTo: $element.parent(),
                        delay: autocompleteOptions.delay,
                        position: autocompleteOptions.position,
                        minLength: autocompleteOptions.minLength,
                        create: function create() {
                            (0, _jquery2.default)(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                                return self.getAutoCompeleteItemFormat(ul, item, this.term, type);
                            };
                        },
                        source: function source(request, response) {
                            var stateSelectValue = self.getStateSelectValue($form);
                            if (self.options.restrictByState && stateSelectValue) {
                                var _Store$emit;

                                _store.Store.emit(_store.Events.POST_FETCH_DATA_START, self.options.url, (_Store$emit = {}, _defineProperty(_Store$emit, type, this.term), _defineProperty(_Store$emit, 'country_code', $form.find(self.options.country).val()), _defineProperty(_Store$emit, 'region', stateSelectValue), _Store$emit), response, type);
                            } else {
                                var _Store$emit2;

                                _store.Store.emit(_store.Events.POST_FETCH_DATA_START, self.options.url, (_Store$emit2 = {}, _defineProperty(_Store$emit2, type, this.term), _defineProperty(_Store$emit2, 'country_code', $form.find(self.options.country).val()), _Store$emit2), response, type);
                            }
                        },
                        change: function change(event, ui) {
                            // clear current field if nothing selected
                            if (ui.item === null) {
                                $element.val('');
                                self.triggerAdditionalEvents($element, $form);
                            }
                        },
                        // TODO: autocomplete only necessary fields iteratively
                        select: function select(event, ui) {
                            self.setStateValue(ui.item.stateName, $form);
                            self.triggerAdditionalEvents(self.options.stateSelect, $form);

                            $form.find(self.options[relativeElement]).val(ui.item[relativeElement]);
                            self.triggerAdditionalEvents(self.options[relativeElement], $form);

                            // autofill current field manually and prevent bubbling (see jQuery autocomplete source)
                            $element.val(ui.item.value);
                            self.triggerAdditionalEvents($element, $form);

                            return false;
                        },
                        response: function response(event, ui) {
                            (0, _jquery2.default)(this).removeClass('ui-autocomplete-loading');
                        }
                    });
                    _store.Store.emit(_store.Events.POST_INITIALIZED, $element);
                }
            }
        }, {
            key: 'isAllowedCountry',
            value: function isAllowedCountry($form) {
                var $country = $form.find(this.options.country),
                    allowableCountries = this.options.allowableCountries.split(','),
                    countryFlag = false;
                if ($country.length) {
                    (0, _jquery2.default)(allowableCountries).each(function (index, item) {
                        if ($country.val() === item) {
                            countryFlag = true;
                            return true;
                        }
                    });
                    if (countryFlag) {
                        return true;
                    }
                }
                return false;
            }
        }, {
            key: 'isApplicableAttribute',
            value: function isApplicableAttribute() {
                var country = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
                var atribute = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '';

                var countryFlag = false,
                    attributesArray = this.options.countryAttributes[country];
                if (country && attributesArray.length) {
                    _jquery2.default.each(attributesArray, function (index, value) {
                        if (value.indexOf(atribute) != -1) {
                            countryFlag = true;
                            return true;
                        }
                    });
                    if (countryFlag) {
                        return true;
                    }
                }
                return false;
            }
        }, {
            key: 'getAutoCompeleteItemFormat',
            value: function getAutoCompeleteItemFormat(ul, item, term, type) {
                return (0, _jquery2.default)('<li class="item">').append((0, _jquery2.default)('<a class="link">').html(this.getAutoCompeleteItemContent(item, term, type))).appendTo(ul);
            }
        }, {
            key: 'getAutoCompeleteItemContent',
            value: function getAutoCompeleteItemContent(item, term, type) {
                var regex = new RegExp('(' + term + ')', 'i'),
                    termTemplate = '<span class="term">$1</span>';

                if (type === 'postcode') {
                    var postcode = item.postcode.replace(regex, termTemplate);
                    return postcode + ' ' + item.suburb + ' ' + item.stateName;
                } else {
                    var suburb = item.suburb.replace(regex, termTemplate);
                    return suburb + ' ' + item.postcode + ' ' + item.stateName;
                }
            }
        }, {
            key: 'setStateValue',
            value: function setStateValue() {
                var value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : '';
                var $form = arguments[1];

                if (!this.isApplicableAttribute($form.find(this.options.country).val(), 'region')) {
                    return true;
                }
                var self = this,
                    $input = $form.find(this.options.stateInput);
                if ($input.is(':visible')) {
                    $input.val(value);
                } else {
                    $form.find(this.options.stateSelect + ' option').each(function (i, element) {
                        var $element = (0, _jquery2.default)(element),
                            elementText = $element.text();
                        if (elementText.length && elementText.toLowerCase() === value.toLowerCase()) {
                            $form.find(self.options.stateSelect).val($element.attr('value'));
                        }
                    });
                }
            }
        }, {
            key: 'getStateSelectValue',
            value: function getStateSelectValue($form) {
                var $select = $form.find(this.options.stateSelect);
                if ($select.is(':visible') && $select.val()) {
                    return $select.find('option:selected').text();
                }
                return false;
            }
        }, {
            key: 'triggerAdditionalEvents',
            value: function triggerAdditionalEvents(element, $form) {
                _underscore2.default.each(this.options.additionalElementEvents, function (event) {
                    $form.find(element).not(':hidden').trigger(event);
                });
            }
        }, {
            key: 'initialized',
            value: function initialized($element) {}
        }, {
            key: 'destroyed',
            value: function destroyed($element) {}
        }, {
            key: 'progress',
            value: function progress(data) {}
        }, {
            key: 'success',
            value: function success(data, response, valueType) {
                if (response !== undefined) {
                    response(_jquery2.default.map(data, function (value, key) {
                        return {
                            value: valueType === 'postcode' ? value.postcode : value.suburb,
                            postcode: value.postcode,
                            suburb: value.suburb,
                            stateName: value.state.state
                        };
                    }));
                }
            }
        }, {
            key: 'error',
            value: function error(data) {}
        }]);

        return Post;
    }();

    exports.default = Post;
    module.exports = exports['default'];
});
