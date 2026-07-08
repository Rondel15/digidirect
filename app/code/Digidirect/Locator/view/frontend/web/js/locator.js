/* global google */
define([
    'jquery',
    'mage/template',
    'Digidirect_Locator/js/action/set-locations',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/lib/core/events',
    'text!Digidirect_Locator/template/info-box.html',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'jquery/validate'
], function ($, mageTemplate, setLocations, modal, events, infoBoxTmpl, alert) {
    'use strict';

    $.widget('digidirect.locator', {
        options: {
            google: {
                key: '',
                libraries: '&libraries=places,geometry'
            },
            entityName: 'store',
            defaultAddress: 'Australia',
            defaultCountryCode: 'AU',
            availableCountries: [],
            defaultLocations: {},
            search: {
                form: '.locator-search .form',
                term: '.locator-search .input-search',
                radius: '.locator-search .radius',
                onLoad: false,
                mode: 'RADIUS'
            },
            map: {
                id: 'locator-map',
                settings: {}
            },
            mapInline: {
                enable: false,
                item: {}
            },
            marker: {
                useClustering: true,
                clusteringOptions: {
                    imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                },
                settings: {}
            },
            radius: {
                enable: true,
                default: 25,
                extensible: false,
                allMarkers: false,
                settings: {},
                conversionConstant: 1.609344,
                metricType: 'km'
            },
            infoBox: {
                template: infoBoxTmpl,
                extraData: {}
            },
            openInPopup: false,
            popupSelector: '[data-role=storelocator-popup]',
            popupOptions: {},
            geoLocation: {
                enable: false,
                zoom: 12,
                action: '[data-role=my-geolocation]',
                noPermissionMessage: '<p>Google Maps does not have permission to use your location.</p><a href="https://support.google.com/maps/answer/2839911" title="Learn more">Learn more</a>',
                markerSettings: {},
                conversionConstant: 0.609344
            },
            autocomplete: {
                useRestrictions: true
            }
        },
        _create: function () {
            this.isLoad = false;
            this.isIgnoreRadius = false;
            this.isMarkerClusterReady = false;
            this.list = [];

            if (window.digidirectGoogleMapsUrl || this.options.google.key) {
                this._loadGoogleApi(window.digidirectGoogleMapsUrl || '//maps.googleapis.com/maps/api/js?loading=async&key=' + this.options.google.key + this.options.google.libraries);
            } else {
                console.warn('Google Map hasn\'t been loaded');
            }

            this._bind();

            if (this.options.search.onLoad) {
                this._searchOnLoad();
            }
        },
        _bind: function () {
            var self = this;
            $(this.options.search.form).on('submit', function (e) {
                var $this = $(this);
                e.preventDefault();
                if ($this.valid() && !self.isLoad) {
                    self._search($this);
                }
            });

            if (this.options.openInPopup) {
                this.modal = modal(this.options.popupOptions, $(this.options.popupSelector));
                events.on('location.show', this.modal.openModal.bind(this.modal));
            }

            this.bindCurrentPosition();
        },
        _loadGoogleApi: function (mapUrl) {
            require([mapUrl], function () {
                this._initMap();
                this.initAutoComplete(this.options.search.term, this.options.autocomplete.useRestrictions);
                this._setInlineMap();
            }.bind(this), function () {
                console.error('Failed to load Google Maps API');
                // to trigger search if necessary
                $(document).trigger('locator.map.initialized');
            });
        },
        initAutoComplete: function (field, useRestrictions) {
            if ($(field).length) {
                this.autocomplete = new google.maps.places.Autocomplete((document.querySelector(field)), {types: ['geocode']});
                if (useRestrictions) {
                    this.autocomplete.setComponentRestrictions({
                        'country': this.options.availableCountries
                    });
                }
            }
        },
        _initMap: function () {
            var options = $.extend({}, this.getMapSettings(), this.options.map.settings);

            if (this.options.openInPopup) {
                events.on('location.show', function (location, settings) {
                    var center = new google.maps.LatLng(location.latitude, location.longitude);

                    this.renderOnMap([location], center, settings);
                }.bind(this));
            }

            this.markers = [];
            this.map = new google.maps.Map(document.getElementById(this.options.map.id), options);
            this.geocoder = new google.maps.Geocoder();
            this.infoWindow = new google.maps.InfoWindow();
            this.bounds = new google.maps.LatLngBounds();

            this.setDefaultCountry();
            this.setGeoLocation();
            $(document).trigger('locator.map.initialized');
        },
        setGeoLocation: function () {
            if (this.options.geoLocation.enable) {
                this._setUserGeoLocation();
            }
        },
        _setUserGeoLocation: function (isManual) {
            var self = this;

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function (position) {
                    self.onSuccessGeoLocation(position);
                }, function (error) {
                    self.onErrorGeoLocation(error, isManual);
                });
            } else {
                console.warn('The browser does not support Geolocation.');
            }
        },
        onSuccessGeoLocation: function (position) {
            var self = this,
                latLng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);

            this.userCurrentLocation = latLng;

            if (!$.isEmptyObject(this.options.defaultLocations)) {
                if (this.isReadyDefaultCountry) {
                    this.setCenterByCurrentPosition(latLng);
                } else {
                    $(document).on('locator.after.setDefaultCountry', function () {
                        self.setCenterByCurrentPosition(latLng);
                    });
                }
            } else {
                this.setCenterByCurrentPosition(latLng);
            }

            this.setGeoLocationMarker(latLng);
        },
        onErrorGeoLocation: function (error, isManual) {
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    if (isManual) {
                        alert({content: this.options.geoLocation.noPermissionMessage});
                    } else {
                        console.warn('User denied the request for Geolocation.');
                    }
                    break;
                case error.POSITION_UNAVAILABLE:
                    alert({content: 'Location information is unavailable.'});
                    break;
                case error.TIMEOUT:
                    alert({content: 'The request to get user location timed out.'});
                    break;
                case error.UNKNOWN_ERROR:
                    alert({content: 'An unknown error occurred.'});
                    break;
            }
        },
        setGeoLocationMarker: function (latLng) {
            var options = $.extend({}, this.getMarkerSettings({}, latLng), this.options.geoLocation.markerSettings);

            this.geoLocationMarker = new google.maps.Marker(options);
        },
        setCenterByCurrentPosition: function (latLng) {
            this.map.setCenter(latLng);
            if (this.options.geoLocation.zoom) {
                this.map.setZoom(this.options.geoLocation.zoom);
            }

            this.sortItemByDistance(latLng);
        },
        sortItemByDistance: function (center) {
            var entity = this.options.defaultLocations[this.options.entityName],
                settings = entity.settings;

            setLocations([], {});

            this.setItemsDistance(center);
            this.list.sort(function (a, b) {
                return a.geo_distance - b.geo_distance;
            });

            setLocations(this.list, settings);
        },
        setItemsDistance: function (center, itemKey) {
            var self = this,
                key = itemKey || 'geo_distance';

            $.each(this.list, function (index, item) {
                var latlng = new google.maps.LatLng(item.latitude, item.longitude),
                    distance = self.calculateDistance(center, latlng) / 1000;
                item[key] = distance;
                item[key + '_formatted'] = self.setFormattedDistance(distance);
            });
        },
        setFormattedDistance: function (distance) {
            return parseFloat(this.getConvertedGeoLocationDistance(distance).toFixed(2)) + ' ' + this.options.radius.metricType;
        },
        bindCurrentPosition: function () {
            var self = this;

            $(this.options.geoLocation.action).on('click', function (e) {
                e.preventDefault();

                self._setUserGeoLocation(true);
            });
        },
        getMap: function () {
            return this.map;
        },
        getMapSettings: function () {
            return {
                zoom: 4,
                center: {lat: -25.271027, lng: 133.595936}
            };
        },
        setDefaultCountry: function () {
            if (!$.isEmptyObject(this.options.defaultLocations)) {
                this.isReadyDefaultCountry = false;
                var entity = this.options.defaultLocations[this.options.entityName],
                    items = entity.items,
                    settings = entity.settings;

                Array.prototype.push.apply(this.list, items);
                setLocations(this.list, settings);

                if (this.options.openInPopup) return;

                this.geocoder.geocode({address: this.options.defaultAddress}, function (results, status) {
                    if (status === google.maps.GeocoderStatus.OK) {
                        this.isIgnoreRadius = true;
                        this.setMarkers(items, results[0].geometry.location, settings);
                        this.isIgnoreRadius = false;
                        this.map.setCenter(results[0].geometry.location);
                        this.defaultBounds = results[0].geometry.viewport;
                        if (this.autocomplete) {
                            this.autocomplete.setBounds(this.defaultBounds);
                        }
                        this.map.fitBounds(this.defaultBounds);
                        $(document).trigger('locator.after.setDefaultCountry');
                        this.isReadyDefaultCountry = true;
                    }
                }.bind(this));
            }
        },
        _search: function ($form) {
            var term = $(this.options.search.term).val(),
                params = {
                    address: term,
                    region: this.options.defaultCountryCode
                };

            if (!!this.defaultBounds) {
                params.bounds = this.defaultBounds;
            }

            this.geocoder.geocode(params, function (results, status) {
                if (status === google.maps.GeocoderStatus.OK) {
                    this.searchByGeocode($form, term, results[0].geometry.location);
                } else if (status === google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
                    this._searchOnQueryLimit($form);
                } else {
                    this.clearAll();
                }
            }.bind(this));
        },
        searchByGeocode: function ($form, term, location) {
            if (this.options.search.mode === 'ALL') {
                this.isIgnoreRadius = true;
                this.renderLocations(this.getAllItems(), location);
            } else if (this.options.search.mode === 'RADIUS') {
                this.renderLocations(this.getInternalRadiusItems(location), location);
            } else {
                this.sendRequest($form, term, location);
            }
        },
        _searchOnLoad: function () {
            var self = this;
            $(this.options.search.term).val(this.options.defaultAddress);

            if (this.options.openInPopup) {
                this._search($(this.options.search.form));
                return;
            }

            $(document).on('locator.map.initialized', function () {
                self._search($(self.options.search.form));
            });
        },
        _searchOnQueryLimit: function ($form) {
            var self = this,
                $submitSearch = $form.find('button:submit');

            $submitSearch.prop('disabled', true);
            setTimeout(function () {
                $submitSearch.prop('disabled', false);
                self._search($form);
            }, 2000);
        },
        clearAll: function () {
            this.clearLocations();
            this.clearMap();
            setLocations([], {});
        },
        _getDefaultSearchParams: function ($form, term, location) {
            return {
                isAjax: true,
                blockName: $form.data('locator'),
                search: term, // TODO: param must be searchTerm, leave for now for backward compatibility
                latitude: location.lat(),
                longitude: location.lng(),
                radius: this.getConvertedDistance()
            };
        },
        getConvertedDistance: function (data) {
            var distance = data || $(this.options.search.radius).val();
            return this.options.radius.metricType === 'km' ? distance : distance * this.options.radius.conversionConstant;
        },
        getConvertedGeoLocationDistance: function (distance) {
            return this.options.radius.metricType === 'km' ? distance : distance * this.options.geoLocation.conversionConstant;
        },
        sendRequest: function ($form, term, location) {
            var self = this,
                jqxhr,
                defaultParams = this._getDefaultSearchParams($form, term, location),
                formData = $form.serializeArray().reduce(function (res, v) {
                    if (v.name === 'radius') {
                        v.name = self.getConvertedDistance(v.value);
                    } else {
                        res[v.name] = v.value;
                    }
                    return res;
                }, defaultParams);

            self.isLoad = true;

            jqxhr = $.ajax({
                url: $form.attr('action'),
                type: 'GET',
                dataType: 'json',
                showLoader: true,
                data: formData
            });

            this.onSendRequest(jqxhr, location);

            return jqxhr;
        },
        onSendRequest: function (jqxhr, location) {
            var self = this;

            jqxhr.done(function (response) {
                self.renderLocations(response.result[self.options.entityName], location);
            }).fail(function (xhr) {
                console.warn('Search failed: ', xhr.statusText);
            }).always(function () {
                self.isLoad = false;
            });
        },
        _setInlineMap: function () {
            var item = this.options.mapInline.item;
            if (this.options.mapInline.enable && item.latitude && item.longitude) {
                this.list.push(item);
                this.initMarker(item, { lat: +item.latitude, lng: +item.longitude });
                this.fitMarkers();
            }
        },
        renderLocations: function (entity, center) {
            var items = entity.items,
                settings = entity.settings;

            this.clearLocations();

            Array.prototype.push.apply(this.list, items);
            if (this.userCurrentLocation) {
                this.setItemsDistance(this.userCurrentLocation);
            }

            if (this.options.search.mode === 'RADIUS' && this.options.sortOrder === 'DISTANCE') {
                this.setItemsDistance(center, 'radius_distance');
                this.list.sort(function (a, b) {
                    return a.radius_distance - b.radius_distance;
                });
            }

            setLocations(this.list, settings);

            // render on map explicitly
            if (this.options.openInPopup) return;

            if (this.map !== undefined) {
                this.renderOnMap(items, center, settings);
                if (this.userCurrentLocation) {
                    this.setGeoLocationMarker(this.userCurrentLocation);
                }
            }
        },
        renderOnMap: function (items, center, settings) {
            this.clearMap();
            this.setMarkers(items, center, settings);
            this.initCircle(center);
            this.fitLocations();
        },
        getAllItems: function () {
            if (!$.isEmptyObject(this.options.defaultLocations)) {
                var entity = this.options.defaultLocations[this.options.entityName];

                return {
                    items: entity.items,
                    settings: entity.settings
                };
            } else {
                return {
                    items: [],
                    settings: {}
                };
            }
        },
        getInternalRadiusItems: function (center) {
            var self = this,
                result = this.getAllItems(),
                radius,
                latlng,
                items = [];

            if (!$.isEmptyObject(this.options.defaultLocations)) {
                radius = this.getRadius();

                $.each(result.items, function (index, item) {
                    latlng = new google.maps.LatLng(item.latitude, item.longitude);
                    if (self.calculateDistance(center, latlng) <= radius) {
                        items.push(item);
                    }
                });
                result.items = items;
            }
            return result;
        },
        setMarkers: function (items, center, settings) {
            var self = this;

            $.each(items, function (index, item) {
                self.createMarker(item, center, settings);

                if (item.child_items) {
                    $.each(item.child_items, function (i, child) {
                        self.createMarker(child, center, settings);
                    });
                }
            });

            this.setMarkerClustering();
        },
        createMarker: function (item, center, settings) {
            var latlng = new google.maps.LatLng(item.latitude, item.longitude);

            if (this.options.radius.enable && !this.isIgnoreRadius) {
                if (this.options.radius.allMarkers || (this.calculateDistance(center, latlng) <= this.getRadius())) {
                    this.initMarker(item, latlng, settings);
                }
            } else {
                this.initMarker(item, latlng, settings);
            }
        },
        initMarker: function (item, latlng) {
            var self = this,
                options = $.extend({}, this.getMarkerSettings(item, latlng), this.options.marker.settings),
                marker = new google.maps.Marker(options);

            if (!$.isEmptyObject(item)) {
                google.maps.event.addListener(marker, 'click', function () {
                    self.infoWindow.setContent(self.getInfoBoxTemplate(item));
                    self.infoWindow.open(self.map, marker);
                });
            }
            this.markers.push(marker);
            this.bounds.extend(latlng);
        },
        getMarkerSettings: function (item, latlng) {
            return {
                map: this.map,
                position: this.fixSameCoordinates(latlng),
                title: item.name
            };
        },
        initCircle: function (center) {
            if (this.options.radius.enable) {
                this.extendRadius(this.markers);
                var options = $.extend({}, this.getCircleSettings(center), this.options.radius.settings);
                this.circle = new google.maps.Circle(options);
                this.markers.push(this.circle);
            }
        },
        getCircleSettings: function (center) {
            return {
                center: center,
                radius: this.getRadius(),
                map: this.map
            };
        },
        getInfoBoxTemplate: function (item, settings) {
            return mageTemplate(this.options.infoBox.template, {
                data: item,
                settings: settings,
                extra: this.options.extraData
            });
        },
        calculateDistance: function (center, latlng) {
            return google.maps.geometry.spherical.computeDistanceBetween(center, latlng);
        },
        compareDistance: function (a, b) {
            if (a.distance < b.distance) {
                return -1;
            }
            if (a.distance > b.distance) {
                return 1;
            }
            return 0;
        },
        getRadius: function () {
            var $radius = $(this.options.search.radius);
            if ($radius.length && $radius.val()) {
                return this.convertRadius($radius.val());
            }
            return this.convertRadius(this.options.radius.default);
        },
        /**
         * Convert radius to meters
         * @param radius
         * @returns {number}
         */
        convertRadius: function (radius) {
            return this.options.radius.metricType === 'km' ? radius * 1000 : radius * 1000 * this.options.radius.conversionConstant;
        },
        extendRadius: function (markers) {
            var $radius = $(this.options.search.radius),
                $nextRadius;
            if (this.options.radius.extensible && !markers.length && $radius.length) {
                $nextRadius = $radius.find('option:selected').next();
                if ($nextRadius.length) {
                    $nextRadius.prop('selected', true);
                    this._search($(this.options.search.form));
                }
            }
        },
        /**
         * Fit and Pan marker(s) and circle to Bounds
         */
        fitLocations: function () {
            if (this.options.radius.enable) {
                if (this.options.radius.allMarkers) {
                    this.fitMarkers();
                } else {
                    this.map.fitBounds(this.circle.getBounds());
                    this.map.panToBounds(this.circle.getBounds());
                }
            } else if (this.markers.length) {
                this.fitMarkers();
            }
        },
        /**
         * Fit and Pan marker(s) to Bounds
         */
        fitMarkers: function () {
            this.map.fitBounds(this.bounds);
            this.map.panToBounds(this.bounds);
        },
        /**
         * Clear previously displayed locations
         */
        clearLocations: function () {
            this.list.length = 0;
        },
        clearMap: function () {
            this.infoWindow.close();
            for (var i = 0; i < this.markers.length; i++) {
                this.markers[i].setMap(null);
            }

            this.markers.length = 0;
            if (this.markerCluster) {
                this.markerCluster.clearMarkers();
            }
        },
        loadMarkerClustering: function () {
            if (this.options.marker.useClustering) {
                require(['markerClustering'], function (MarkerClusterer) {
                    this.isMarkerClusterReady = true;
                    this.clusterJS = MarkerClusterer;
                    this.initMarkerClustering(this.clusterJS);
                }.bind(this), function () {
                    console.error('Failed to load Marker Clustering');
                });
            }
        },
        initMarkerClustering: function (MarkerClusterer) {
            if (this.options.marker.useClustering) {
                this.markerCluster = new MarkerClusterer(this.map, this.markers, this.options.marker.clusteringOptions);
            }
        },
        setMarkerClustering: function () {
            if (this.isMarkerClusterReady) {
                this.initMarkerClustering(this.clusterJS);
            } else {
                this.loadMarkerClustering();
            }
        },
        fixSameCoordinates: function (latlng) {
            var self = this,
                newLat,
                newLng,
                finalLatLng = latlng;

            if (this.options.marker.useClustering) {
                $.each(this.markers, function (i, marker) {
                    // if a marker already exists in the same position as this marker
                    if (typeof marker.getPosition !== 'undefined' && marker.getPosition().equals(latlng)) {
                        var a = 360.0 / self.markers.length;
                        newLat = latlng.lat() + -0.00004 * Math.cos((+a * i) / 180 * Math.PI);
                        newLng = latlng.lng() + -0.00004 * Math.sin((+a * i) / 180 * Math.PI);
                        finalLatLng = new google.maps.LatLng(newLat, newLng);
                    }
                });
            }

            return finalLatLng;
        }
    });

    return $.digidirect.locator;
});
