import $ from 'jquery';
import _ from 'underscore';
import {Store, Events} from './../common/store';

export default class Post {
    constructor (options) {
        this.options = Object.assign({}, this.options, options);

        this.bind();
        this.watchers();
    }

    bind () {
        this.initializeAutocomplete();
        this.handleCountryChange();
        this.handleStateChange();
    }

    watchers () {
        Store.on(Events.POST_INITIALIZED, ($element) => this.initialized($element));
        Store.on(Events.POST_DESTROYED, ($element) => this.destroyed($element));
        Store.on(Events.POST_FETCH_DATA_PROGRESS, (data) => this.progress(data));
        Store.on(Events.POST_FETCH_DATA_SUCCESS, (data, response, value) => this.success(data, response, value));
        Store.on(Events.POST_FETCH_DATA_ERROR, (data) => this.error(data));
    }

    initializeAutocomplete () {
        $(this.options.postcode).each(function (index) {
            let $this = $(this);
            $this.trigger('blur');
        });

        this.setPostcodeAutocomplete();
        this.setSuburbAutocomplete();
    }

    destroyAutocomplete ($form) {
        let self = this,
            $suburb = $form.find(self.options.suburb),
            $postcode = $form.find(self.options.postcode);

        if ($suburb.data('ui-autocomplete') !== undefined) {
            $suburb.autocomplete('destroy');
            Store.emit(Events.POST_DESTROYED, $suburb);
        }
        if ($postcode.data('ui-autocomplete') !== undefined) {
            $postcode.autocomplete('destroy');
            Store.emit(Events.POST_DESTROYED, $postcode);
        }
    }

    handleCountryChange () {
        let self = this;

        $(document).on('change', self.options.country, function () {
            let $form = $(this).closest('form');
            if (self.isAllowedCountry($form)) {
                self.initializeAutocomplete();
            } else {
                self.destroyAutocomplete($form);
            }
        });
    }

    handleStateChange () {
        let self = this;
        $(document).on('change', this.options.stateSelect, function () {
            let $form = $(this).closest('form');
            if (self.isAllowedCountry($form) && self.isApplicableAttribute($form.find(self.options.country).val(), 'region')) {
                $form.find(`${self.options.postcode}, ${self.options.suburb}`).val('');
            }
        });
    }

    setSuburbAutocomplete () {
        let self = this;

        $(document).on('focus', self.options.suburb, function () {
            let $this = $(this),
                $form = $this.closest('form');
            self.assignAutocomplete($this, $form, 'suburb', 'postcode');
        });
    }

    setPostcodeAutocomplete () {
        let self = this;

        $(document).on('focus', self.options.postcode, function () {
            let $this = $(this),
                $form = $this.closest('form');
            self.assignAutocomplete($this, $form, 'postcode', 'suburb');
        });
    }

    /**
     * Assign Autocomplete to element
     * @param $element
     * @param $form
     * @param type
     * @param relativeElement
     */
    assignAutocomplete ($element, $form, type, relativeElement) {
        let self = this,
            autocompleteOptions = self.options.autocomplete;

        if ($element.data('ui-autocomplete') === undefined && self.isAllowedCountry($form)) {
            $element.autocomplete({
                appendTo: $element.parent(),
                delay: autocompleteOptions.delay,
                position: autocompleteOptions.position,
                minLength: autocompleteOptions.minLength,
                create: function () {
                    $(this).data('ui-autocomplete')._renderItem = function (ul, item) {
                        return self.getAutoCompeleteItemFormat(ul, item, this.term, type);
                    };
                },
                source: function (request, response) {
                    let stateSelectValue = self.getStateSelectValue($form);
                    if (self.options.restrictByState && stateSelectValue) {
                        Store.emit(Events.POST_FETCH_DATA_START, self.options.url, {[type]: this.term, 'country_code': $form.find(self.options.country).val(), 'region': stateSelectValue}, response, type);
                    } else {
                        Store.emit(Events.POST_FETCH_DATA_START, self.options.url, {[type]: this.term, 'country_code': $form.find(self.options.country).val()}, response, type);
                    }
                },
                change: function (event, ui) {
                    // clear current field if nothing selected
                    if (ui.item === null) {
                        $element.val('');
                        self.triggerAdditionalEvents($element, $form);
                    }
                },
                // TODO: autocomplete only necessary fields iteratively
                select: function (event, ui) {
                    self.setStateValue(ui.item.stateName, $form);
                    self.triggerAdditionalEvents(self.options.stateSelect, $form);

                    $form.find(self.options[relativeElement]).val(ui.item[relativeElement]);
                    self.triggerAdditionalEvents(self.options[relativeElement], $form);

                    // autofill current field manually and prevent bubbling (see jQuery autocomplete source)
                    $element.val(ui.item.value);
                    self.triggerAdditionalEvents($element, $form);

                    return false;
                },
                response: function (event, ui) {
                    $(this).removeClass('ui-autocomplete-loading');
                }
            });
            Store.emit(Events.POST_INITIALIZED, $element);
        }
    }

    isAllowedCountry ($form) {
        let $country = $form.find(this.options.country),
            allowableCountries = this.options.allowableCountries.split(','),
            countryFlag = false;
        if ($country.length) {
            $(allowableCountries).each(function (index, item) {
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

    isApplicableAttribute (country = '', atribute = '') {
        let countryFlag = false,
            attributesArray = this.options.countryAttributes[country];
        if (country && attributesArray.length) {
            $.each(attributesArray, function (index, value) {
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

    getAutoCompeleteItemFormat (ul, item, term, type) {
        return $('<li class="item">').append($('<a class="link">').html(this.getAutoCompeleteItemContent(item, term, type))).appendTo(ul);
    }

    getAutoCompeleteItemContent (item, term, type) {
        let regex = new RegExp('(' + term + ')', 'i'),
            termTemplate = '<span class="term">$1</span>';

        if (type === 'postcode') {
            let postcode = item.postcode.replace(regex, termTemplate);
            return `${postcode} ${item.suburb} ${item.stateName}`;
        } else {
            let suburb = item.suburb.replace(regex, termTemplate);
            return `${suburb} ${item.postcode} ${item.stateName}`;
        }
    }

    setStateValue (value = '', $form) {
        if (!this.isApplicableAttribute($form.find(this.options.country).val(), 'region')) {
            return true;
        }
        let self = this,
            $input = $form.find(this.options.stateInput);
        if ($input.is(':visible')) {
            $input.val(value);
        } else {
            $form.find(`${this.options.stateSelect} option`).each(function (i, element) {
                let $element = $(element),
                    elementText = $element.text();
                if (elementText.length && elementText.toLowerCase() === value.toLowerCase()) {
                    $form.find(self.options.stateSelect).val($element.attr('value'));
                }
            });
        }
    }

    getStateSelectValue ($form) {
        let $select = $form.find(this.options.stateSelect);
        if ($select.is(':visible') && $select.val()) {
            return $select.find('option:selected').text();
        }
        return false;
    }

    triggerAdditionalEvents (element, $form) {
        _.each(this.options.additionalElementEvents, function (event) {
            $form.find(element).not(':hidden').trigger(event);
        });
    }

    initialized ($element) {

    }

    destroyed ($element) {

    }

    progress (data) {

    }

    success (data, response, valueType) {
        if (response !== undefined) {
            response($.map(data, function (value, key) {
                return {
                    value: (valueType === 'postcode') ? value.postcode : value.suburb,
                    postcode: value.postcode,
                    suburb: value.suburb,
                    stateName: value.state.state
                };
            }));
        }
    }

    error (data) {

    }
}
