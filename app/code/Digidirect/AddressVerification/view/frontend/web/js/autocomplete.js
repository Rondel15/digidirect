define([
    'jquery',
    'rjsResolver',
    './dist/common/component',
    'jquery/ui',
    'validation'
], function ($, resolver, Component) {
    'use strict';

    $.widget('digidirect.addressVerification', {
        options: {
            mode: 'googleapi',
            post: {
                url: '',
                postcode: '[name="postcode"]',
                suburb: '[name="city"]',
                stateSelect: '[name="region_id"]',
                stateInput: '[name="region"]',
                country: '[name="country_id"]',
                restrictByState: false,
                allowableCountries: 'AU,NZ',
                countryAttributes: {
                    'AU': ['postcode', 'region', 'city'],
                    'NZ': ['postcode', 'city']
                },
                additionalElementEvents: ['change'],
                autocomplete: {
                    delay: 300,
                    minLength: 1,
                    position: {}
                }
            },
            gplaces_config: {
                api_key: '',
                countries: 'AU,US,NZ',
                default_country: 'AU'
            },
            inputField: 'street[]',
            selectors: {
                route: 'street[]',
                street_number: 'street[]',
                locality: 'city',
                postal_code: 'postcode',
                administrative_area_level_1: 'region_id',
                administrative_area_level_alter: 'region',
                country: 'country_id'
            },
            config: {
                route: 'long_name',
                subpremise: 'short_name',
                street_number: 'short_name',
                locality: 'long_name',
                administrative_area_level_1: 'long_name',
                postal_code: 'short_name',
                country: 'short_name'
            },
            viewCustom: ''
        },

        _initAfterAddressFormLoaded: function (method) {
            var self = this;
            $(document).on('av_address_form_loaded', function () {
                switch (method) {
                    case 'post':
                        new Component(self.options.post, method);
                        break;
                    default:
                        new Component(self.options, self.options.mode);
                }
            });
        },

        _create: function () {
            var self = this;
            resolver(function () {
                switch (self.options.mode) {
                    case 'post':
                        new Component(self.options.post, self.options.mode);
                        self._initAfterAddressFormLoaded(self.options.mode);
                        break;
                    default:
                        if (self.options.gplaces_config.api_key) {
                            new Component(self.options, self.options.mode);
                            self._initAfterAddressFormLoaded(self.options.mode);
                        } else {
                            console.warn('Address Autocomplete: API key hasn\'t been passed');
                        }
                }
            });
        }
    });

    return $.digidirect.addressVerification;
});
