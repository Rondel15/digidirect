/* global google */
define([
    'jquery',
    'mage/translate',
    'locator',
    'jquery/ui'
], function ($) {
    'use strict';

    $.widget('digidirect.locatorDirections', {
        options: {
            locator: '.store-view',
            search: {
                form: '.direction-search',
                mode: '.direction-mode .radio',
                from: '.direction-from',
                to: '.direction-to'
            },
            panel: '#direction-panel',
            message: $.mage.__('At least one of the origin, destination, or waypoints could not be geocoded.'),
            settings: {},
            reverse: '.reverse-directions',
            autocomplete: {
                useRestrictions: false
            }
        },
        _create: function () {
            this.locator = $(this.options.locator).data('digidirect-locator');
            this._bind();
        },
        _bind: function () {
            var self = this;
            $(document).on('locator.map.initialized', $.proxy(this._onInitMap, this));

            $(this.options.search.form).on('submit', function (e) {
                e.preventDefault();
                if ($(this).valid()) {
                    self.renderRoute();
                }
            });

            $(this.options.search.mode).on('change', function () {
                if ($(self.options.search.from).val() && $(self.options.search.to).val()) {
                    self.renderRoute();
                }
            });

            $(this.options.reverse).on('click', $.proxy(this.reverseDirections, this));
        },
        _onInitMap: function () {
            this._initDirections();
            this._setUserGeoLocation();
            this.locator.initAutoComplete(this.options.search.from, this.options.autocomplete.useRestrictions);
            this.locator.initAutoComplete(this.options.search.to, this.options.autocomplete.useRestrictions);
        },
        _initDirections: function () {
            this.directionsDisplay = new google.maps.DirectionsRenderer();
            this.directionsService = new google.maps.DirectionsService();
            var map = this.locator.getMap();

            this.directionsDisplay.setMap(map);
            this.directionsDisplay.setPanel(document.querySelector(this.options.panel));
        },
        renderRoute: function () {
            var self = this,
                selectedMode = $(self.options.search.mode + ':checked').val(),
                $panel = $(document.querySelector(self.options.panel)),
                options = $.extend({}, this.getDirectionsSettings(selectedMode), this.options.settings);

            this.directionsService.route(options, function (response, status) {
                switch (status) {
                    case 'OK':
                        $panel.removeClass('-no-results').html('');
                        self.directionsDisplay.setDirections(response);
                        break;
                    case 'NOT_FOUND':
                        self.directionsDisplay.setDirections({routes: []});
                        $panel.html(self.options.message).addClass('-no-results');
                        break;
                    default:
                        self.directionsDisplay.setDirections({routes: []});
                        console.warn('Directions request failed due to ' + status);
                        $panel.addClass('-no-results').html('');
                }
            });
        },
        getDirectionsSettings: function (selectedMode) {
            var DEFAULT_MODE = 'DRIVING';
            return {
                origin: $(this.options.search.from).val(),
                destination: $(this.options.search.to).val(),
                travelMode: selectedMode ? google.maps.TravelMode[selectedMode] : DEFAULT_MODE
            };
        },
        _setUserGeoLocation: function () {
            var self = this;
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    var geocoder = new google.maps.Geocoder(),
                        latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

                    geocoder.geocode({ 'latLng': latLng }, function (results, status) {
                        if (status == google.maps.GeocoderStatus.OK && results[0]) {
                            $(self.options.search.from).val(results[0].formatted_address);
                        }
                    });
                });
            } else {
                console.warn('The browser does not support Geolocation.');
            }
        },
        reverseDirections: function () {
            var $from = $(this.options.search.from),
                $to = $(this.options.search.to),
                fromVal = $from.val(),
                toVal = $to.val();

            $from.val(toVal);
            $to.val(fromVal);

            if ($from.val() && $to.val()) {
                $(this.options.search.form).trigger('submit');
            }
        }
    });

    return $.digidirect.locatorDirections;
});
