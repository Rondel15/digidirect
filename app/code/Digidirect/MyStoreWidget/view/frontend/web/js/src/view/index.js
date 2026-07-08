/* global google */
import $ from 'jquery';

export default class View {
    constructor (options) {
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
    loadGoogleLibrary () {
        let googleMapsUrl = window.digidirectGoogleMapsUrl,
            mapUrl;
        try {
            if (googleMapsUrl !== undefined) {
                mapUrl = googleMapsUrl;
            } else {
                mapUrl = `//maps.googleapis.com/maps/api/js?loading=async&key=${this.options.googleAutoSuggestApiKey}&libraries=places`;
            }
            require([mapUrl], () => {
                this.init();
            });
        } catch (e) {
            console.warn('Google Places Library hasn\'t been downloaded', e);
        }
    }

    /**
     *  Init Google Autocomplete
     */
    init () {
        let autocomplete = new google.maps.places.Autocomplete($(this.options.input)[0], {types: ['geocode']});

        if (this.options.singleCountryData) {
            autocomplete.setComponentRestrictions(this.options.singleCountryData);
        }

        this.bind(autocomplete);
    }

    /**
     *  Init listeners
     */
    bind (autocomplete) {
        autocomplete.addListener('place_changed', () => this.fillInAddress(autocomplete));
        $(this.options.input).on('focus', () => this.focus());
        $(this.options.input).on('blur', () => this.blur());
        $(this.options.form).on('submit', () => this.submit());
    }

    /**
     *  Clear data
     */
    clearFormData () {
        let prefix = this.options.googleInputPrefix;
        $(this.options.findStoreInput).val(0);
        for (let component in this.componentForm) {
            $(prefix + component).val('');
        }
    }

    /**
     *  Fills fields
     */
    fillInAddress (instanse) {
        let place = instanse.getPlace(),
            prefix = this.options.googleInputPrefix;

        this.clearFormData();

        // Get each component of the address from the place details
        // and fill the corresponding field on the form.
        if (place.address_components) {
            for (let i = 0; i < place.address_components.length; i++) {
                let addressType = place.address_components[i].types[0];
                if (this.componentForm[addressType]) {
                    let val = place.address_components[i][this.componentForm[addressType]];
                    $(prefix + addressType).val(val);
                }
            }
            $(this.options.findStoreInput).val(1);
            this.isSelectedStore = true;
            this.checkSubmitForm();
        }
    }
    /**
     *  Gets geolocation
     */
    geolocate () {
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function (position) {
                let geolocation = {
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

    /**
     *  Focus on input
     */
    focus () {
        this.isSelectedStore = false;
        this.isNeedToSendForm = false;

        if (this.options.isGeoLocationEnabled) {
            $(this.options.input).on('focus', () => this.focus);
        }
    }

    /**
     *  Removes restrictions for submit form
     */
    blur () {
        this.isSelectedStore = true;
    }

    /**
     *  Cancel submit form if google doesn't apply selected address
     */
    submit () {
        let val = $(this.options.input).val();
        if (!this.isSelectedStore && val != '') {
            this.isNeedToSendForm = true;
            return false;
        }
    }

    /**
     *  Submit form after change address
     */
    checkSubmitForm () {
        if (this.isNeedToSendForm) {
            $(this.options.form).submit();
        }
    }
}
