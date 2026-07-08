## Locator Widget Settings

Option | Nested Option | Type | Default | Description
------ | ------------- | ---- | ------- | -----------
google |  | object |  | Google Maps API configurations.
- | key | string | '' | Google Maps API key.
- | libraries | string | '&libraries=geometry' | Google Maps API [libraries](https://developers.google.com/maps/documentation/javascript/libraries).
entityName |  | string | 'store' | Abstract Entity name to select the return response from the backend.
defaultAddress |  | string | 'Australia' | Address displayed on the map by default.
defaultCountryCode |  | string | 'AU' | Country code that influences, not fully restricts, results from the Geocoding service.
availableCountries |  | array | [] | List of available countries from BO to restrict autocomplete.
defaultLocations |  | object | {} | Locations from 'defaultAddress' (country) displayed on the map if the search is not applied.
search |  | object |  | Search configurations.
- | form | string | '.locator-search .form' | Selector for search form element.
- | term | string | '.locator-search .input-search' | Selector for search input element.
- | term | string | '.locator-search .radius' | Selector for search radius element.
- | onLoad | boolean | true | Specifies the use auto-search entity options in the default radius in the default country.
 -| mode | string | 'RADIUS' | Mode of marker(s) and entity(ies) visibility after 'Search' execute. 'RADIUS' - Show all items inside radius only . 'ALL' - Always show all. '' (empty string) - server-side rules: get result from BE by AJAX call ('sendRequest' method).
map |  | object |  | Google Map configurations.
- | id | string | 'locator-map' | Selector for map element.
- | settings | object | {} | Google Map [options](https://developers.google.com/maps/documentation/javascript/tutorial#MapOptions).
mapInline |  | object |  | Google Map (inline) configurations. Used for maps with one marker.
- | enable | boolean | true | Specifies the use inline map logic.
- | item | object | {} | Location data for inline map.
marker |  | object |  | Google Map marker configurations
 -| useClustering | boolean | true | Specifies the use MarkerClusterer.
 -| clusteringOptions | object | {} | Marker Clusterer  [options](https://googlemaps.github.io/js-marker-clusterer/docs/reference.html), [examples](https://googlemaps.github.io/js-marker-clusterer/docs/examples.html).
-| settings | object | {} | Google Map [marker options](https://developers.google.com/maps/documentation/javascript/3.exp/reference#MarkerOptions).
radius |  | object |  | Radius (circle) configurations
- | enable | boolean | true | Specifies the use radius logic.
- | default | integer | 25 | Specifies radius by default.
- | extensible | boolean | false | Specifies automatically extensible, if there are no results found in the selected radius.
- | allMarkers | boolean | false | Specifies the visibility of markers that are outside the radius.
- | settings | object | {} | Google Map [circle options](https://developers.google.com/maps/documentation/javascript/reference#CircleOptions).
- | conversionСonstant | number | 1.609344 | Сonversion rate from km to miles
- | metricType | string| 'km' | Default metric type.
infoBox |  | object |  | Google Map infoBox configurations.
- | template | textResource*/string | infoBoxTmpl | Google Map infoBox template.
- | extraData | object | {} | InfoBox template extra data.
openInPopup |  | string | false | Flag for showing map separately for every location in popup
popupSelector |  | string | '[data-role=storelocator-popup]' | Selector for popup with map and location details inside
popupOptions |  | Object | {} | Options for [modal widget](https://devdocs.magento.com/guides/v2.3/javascript-dev-guide/widgets/widget_modal.html)
geoLocation |  | object |  | User GeoLocation configurations.
- | enable | boolean | false | Specifies the use GeoLocation logic.
- | zoom | boolean | 12 | Specifies the level of zoom after centering the map at the user's current location. Use empty string to disable change level of zoom.
- | action | sting | '[data-role=my-geolocation]' | Selector for CTA element which trigger GeoLocation logic.
- | noPermissionMessage | sting | '<p>Google Maps does not have permission to use your location.</p><a href="https://support.google.com/maps/answer/2839911" title="Learn more">Learn more</a>' | Specifies the message (appear by click on the CTA element - 'action' option) if the user denied the request for GeoLocation.
-| markerSettings | object | {} | Special config for current user GeoLocation marker. Google Map [marker options](https://developers.google.com/maps/documentation/javascript/3.exp/reference#MarkerOptions).
 autocomplete |  | object |  | Autocomplete configurations.
  - | useRestrictions | boolean | false | Specifies the use autocomplete component restriction logic by country code(s) (from 'availableCountries' option).
 
## Locator Directions Widget Settings
 
 Option | Nested Option | Type | Default | Description
 ------ | ------------- | ---- | ------- | -----------
 locator |  | string | '.store-view' | Selector for locator widget element.
 search |  | object |  | Search Directions configurations.
 | form | string | '.direction-search' | Selector for directions search form element.
 | mode | string | '.direction-search' | Selector for directions travel mode element.
 | from | string | '.direction-from' | Selector for directions origin input element.
 | to | string | '.direction-to' | Selector for directions destination input element.
 panel |  | string | '#direction-panel' | Selector for directions result element.
 message |  | string | ```$.mage.__('At least one of the origin, destination, or waypoints could not be geocoded.')``` | Specifies the message if the destination address doesn't have geo coordinates.
 settings |  | object |  | Google Map [Directions options](https://developers.google.com/maps/documentation/javascript/directions#DirectionsRequests).
  reverse |  | string | '.reverse-directions' | Selector for directions reverse element.
  autocomplete |  | object |  | Autocomplete Directions configurations.
  - | useRestrictions | boolean | false | Specifies the use autocomplete component restriction logic by country code(s).
 
 
 \* - A RequireJS/AMD loader plugin - [text!](https://github.com/requirejs/text) 
