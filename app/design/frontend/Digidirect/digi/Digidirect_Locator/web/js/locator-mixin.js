define([
    'jquery',
    'mage/template',
    'Digidirect_Locator/js/action/set-locations',
    'jquery/ui',
    'jquery/validate'
], function ($, mageTemplate, setLocations) {
    'use strict';

    return function (target) {
        $.widget('digidirect.locator', target, {
            initAutoComplete: function (field) {
                if ($(field).length) {
                    this.autocomplete = new google.maps.places.Autocomplete((document.querySelector(field)), {types: ['geocode']});
                    this.autocomplete.addListener('place_changed', this.searchAutoComplete.bind(this));
                }
            },

            setDefaultCountry: function () {
                if (!$.isEmptyObject(this.options.defaultLocations)) {
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
                            // this.map.fitBounds(this.defaultBounds); // Disabled fitBounds fit used wrong zoom
                        }
                    }.bind(this));
                }
            },

            searchAutoComplete: function () {
                this._search($(this.options.search.form))
            },

            extendRadius: function (markers) {
                var $radius = $(this.options.search.radius),
                    $nextRadius;
                if (this.options.radius.extensible && !markers.length && $radius.length) {
                    $nextRadius = $radius.find('option:selected').next();
                    if ($nextRadius.length) {
                        $nextRadius.attr('selected', 'selected');
                        if ($radius.data('selectric')) {
                            $radius.selectric('refresh');
                        }
                        this._search($(this.options.search.form));
                    }
                }
            }
        });

        return $.digidirect.locator;
    };
});
