/* global google */
import $ from 'jquery';
import {Store, Events} from './../common/store';

/** Class representing View of module */
export default class Autocomplete {
    /**
     * Initializing of view-model
     * @param options
     */

    constructor (options) {
        this.options = Object.assign({}, this.options, options);

        ({selectors: this.selectors, config: this.config, inputField: this.inputField} = options);

        // list of form field nodes
        this.nodes = {};
        for (let selector in this.selectors) {
            this.nodes[this.selectors[selector]] = $(`[name="${this.selectors[selector]}"]`).filter(':visible').first()[0];
        }
        // initializing watchers
        this.watchers();

        this._loadGoogleApi(window.digidirectGoogleMapsUrl || `//maps.googleapis.com/maps/api/js?loading=async&key=${options.gplaces_config.api_key}&libraries=places`);

        // Initialization value
        this.isFormComplete = false;
    }

    /**
     * Loading of google places library
     * @private
     */
    _loadGoogleApi (mapUrl) {
        try {
            require([mapUrl], () => {
                this.initAutocomplete(this.inputField, this.config, this.selectors);
            });
        } catch (e) {
            console.warn('Google Places Library hasn\'t been downloaded', e);
        }
    }

    /**
     * Binds events to functions of module
     */
    watchers () {
        Store.on(Events.PLACE_SELECT_PENDING, () => {
            this._getPlace();
        });
        Store.on(Events.FORM_CHANGE_SUCCESS, (data) => this.success(data));
        Store.on(Events.ERROR, (data) => this.error(data));
        Store.on(Events.PLACES_DOWNLOAD_ERROR, (data) => this.error(data));
        Store.on(Events.GOOGLE_MAPS_AUTHENTICATION_ERROR, (data) => this.gmError(data));

        // watches for authentication errors from Google Places API
        window.gm_authFailure = () => {
            Store.emit(Events.GOOGLE_MAPS_AUTHENTICATION_ERROR);
        };
    };

    /**
     * Initializing functionality of Google Places
     * and binds it to the field, configured in options
     * @param selector
     * @param componentForm
     * @param formNames
     */
    initAutocomplete (selector, componentForm, formNames) {
        let self = this,
            service = new google.maps.places.AutocompleteService(),
            isAutocompleteAttribute = 'digidirect-autocomplete',
            node = $(`[name="${selector}"]:not([` + isAutocompleteAttribute + `])`).filter(':visible').first();

        if (node.length == 0) return;

        // initialize autocomplete with node[0]
        this.autocomplete = new google.maps.places.Autocomplete(node[0], {types: ['geocode']});
        $(node[0]).attr(isAutocompleteAttribute, 'on');

        // set restricted country if selected
        node.on('focus', () => {
            this._setRestriction();
        });

        this.autocomplete.addListener('place_changed', () => {
            self._getPlace();
            self.fillInAddress([this.config, this.selectors]);
            self.isFormComplete = true;
        });

        node.on('input', function () {
            let input = this;
            if (input.value) {
                service.getPlacePredictions({input: input.value}, (data, status) => {
                    if (status === 'ZERO_RESULTS' && !self.isFormComplete) {
                    // reset form
                    self.fieldsChanging();
                }
                if (status !== 'OK' && status !== 'ZERO_RESULTS') {
                    Store.emit(Events.PLACES_DOWNLOAD_ERROR);
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
    };

    /**
     * Used for nodes manipulating.
     * If no parameters were passed to function it behave as reset function for nodes.
     * If parameters with address were passed, it is used to fill the nodes of form.
     * @param components
     * @returns {*}
     */
    fieldsChanging (components = []) {
        let checked = {},
            isEmpty,
            stateValue,
            nodeArray = Object.keys(this.nodes),
            subpremise;

        ([components, isEmpty] = Object.keys(components).length ? [components, false] : [nodeArray, true]);

        for (let i = 0, length = Object.keys(components).length; i < length; i++) {
            let type,
                val,
                node;

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
                            $(node).val(val);
                        }
                    } else {
                        if (checked[this.selectors[type]]) {
                            node.value += ` ${val}`;
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
            $(node).trigger('change');
        }
        if (stateValue) {
            return stateValue;
        }
    };

    /**
     * Function used to fill the form, when address was selected from predefined google places
     * @param componentForm
     * @param formNames
     */
    fillInAddress ([componentForm, formNames]) {
        let checked = {},
            self = this,
            stateValue;

        stateValue = self.fieldsChanging(self.place.address_components);

        if (stateValue) {
            let node = self.nodes[this.selectors['administrative_area_level_1']];
            if (node) {
                // choose visible field on default address account and checkout pages
                if ($(node).css('display') === 'none' || $(node).closest('.field').css('display') === 'none') {
                    $(self.nodes[this.selectors['administrative_area_level_alter']])
                        .val(stateValue)
                        .trigger('change');
                    checked.region = true;
                } else {
                    var options = node.options;
                    if (options) {
                        for (let j = 0, length = options.length; j < length; j++) {
                            if (options[j].innerHTML.toLowerCase() === stateValue.toLowerCase()) {
                                options[j].selected = 'selected';
                            }
                        }
                    }
                }
                $(node).trigger('change');
            }
        }
        Store.emit(Events.FORM_CHANGE_SUCCESS);
    };

    /**
     * Used to get all information about preselected address from google.
     * @private
     */
    _getPlace () {
        try {
            this.place = this.autocomplete.getPlace();
        } catch (e) {
            Store.emit(Events.PLACE_SELECT_ERROR);
        }
    };

    /**
     * Set country restriction, if country was selected and allows all countries if one haven't been chosen
     * @private
     */
    _setRestriction () {
        let country = $(this.nodes[this.selectors['country']]).val();
        if (this.options.gplaces_config.countries === 'all') {
            this.autocomplete.setComponentRestrictions({country: []});
        } else if (country) {
            this.autocomplete.setComponentRestrictions({country: country.toLowerCase()});
        } else {
            delete this.autocomplete.componentRestrictions;
        }
    };

    error () {
    };

    success () {
    };

    gmError () {
        let node = $(`[name="${this.inputField}"]`);
        node.attr({placeholder: '', disabled: false}).css('background-image', '');
        google.maps.event.clearInstanceListeners(node[0]);
    }
}
