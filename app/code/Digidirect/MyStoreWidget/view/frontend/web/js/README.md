## My Store Switcher Widget Settings

Option | Type | Default | Description
------ | ---- | ------- | -----------
container | string | '.block-mystorebar' | Selector for store finder bar element.
input | string | '#mystore-input' | Selector for search input element.
form | string | '#mystore-form' | Selector for form.
findStoreInput | string | '#mystore_find_store' | Selector for hidden input indicating that the address is found.
entityId | string | '#abstract_entity_id' | Selector for selected entity ID (store). Designed to request a save.
openAction | string | '[data-action="set-store"]' | Selector for the trigger element which open/show 'container'.
changeAction | string | '[data-action="change-store"]' | Selector for the trigger element which enable change mode.
saveAction | string | '#block-mystorebar [data-action="save-mystore"]' | Selector for the trigger element which save store.
hideSelector | string | '-hide' | Selector for the trigger element which hide 'container'.
selectedSelector | string | '-selected' | Selector for 'container' element which enable 'search' mode.
noResultsSelector | string | '-no-results' | Selector for 'container' element which enable 'no-results' mode.
saveUrl | string | '' | Save URL (AJAX).
isAjaxSave | boolean | false | Specifies the use AJAX to save store.
searchUrl | string | '' | Search URL.
searchNoResults | string | $.mage.__('No matches found.') | No results message for search.
searchResultFormat | string | '%name (%state, %postcode)' | Label format of search results items
isAutocompleteEnabled | boolean | true | Specifies the use of the Autocomplete widget.
isGoogleAutoSuggestEnabled | boolean | false | Specifies the use Google Autocomplete.
googleAutoSuggestApiKey | string | null | Google Auto Suggest Key
singleCountryData | object | null | Setting for Google Auto Complete for current country (if delivery is available for only one country)
autocompleteSettings | object | ``` {appendTo: '#mystore-control', minLength: 0} ``` | jQuery Autocomplete widget settings.
storesList | array | [] | List of available stores.
isGeoLocationEnabled | boolean | false | Specifies the use of the HTML5 GeoLocation API to locate the user.
isNeedKeepGeoLocation | boolean | false | Specifies whether to store data received from the HTML5 GeoLocation API on the client side.
isSelectedStore | boolean | false | Specifies whether the store was saved on the backend side.
noticeTemplate | string | ```'<span class="notice">' + $.mage.__('Sorry, you need allow your browser use Geo location to find a store.') + '</span>'``` | Displayed message if user did not allow HTML5 GeoLocation API. Leave blank if you want to hide it.