[wiki link](https://wiki.digidirect.com/display/LEGO/%5BLocator%5D+Abstract+Entity)


Search Parameters:
(array) specified_ids - to search only specified entities by entity_id
(bool) without_radius - to search without radius limit
(int) limit - to limit search collection

This two parameters required to search with radius limit:
(string) latitude  (example -33.8909078)
(string) longitude (example 151.1385674)
(int) radius - specified radius to limit.  Value in kilometers.
If the radius is not specified, then the default settings will be used.

===========================
IMPORTANT: 
1. Store locator module uses Abstract Entity. To speed up stores search store locator works only with flat tables,
 that means you have to enable index tables for store entity(or any other you use as entity to be displayed on a map)

2. Stores -> Configuration -> Store Locator -> Fulltext Search Attributes Available For entities -> Set store 
if you want to use fulltext search (it faster and more relevant)

3. Make reindex

SOME FEATURES:

If your search request contains coordinates magento searches entities in specified countries

If your search request does not contain coordinates magento searches entities within default country

If your search request contains country filter magento searches entities in specified countries

-- filter by country:
   send country_filter in request parameters
   
   

-- Display specified entities
   send specified_ids in request parameters
   
   

=================================



### VERSION 1.2.0
1. Opening hours is now wysiwyg field

### VERSION 1.3.0 

1. Added helper method to get regions by country

### VERSION 1.3.1

1. Now if you do not need fulltext search you can specify parameter $searchParams['completeCoincidence'] that will 
search with "OR" and "=" condition. It can be useful if you have dropdown with states and you need to filter/search
stores by this state (Case from Nick Scali project)

### VERSION 1.4.0

1. Added ability to show map separately in popup with chosen store


### VERSION 1.4.1
1. Added ability to filter by country. To do it you need to send country codes in 'country_filter' property in requestю
it does not matter if it is (AU,US) or "[AU, US]". To get all countries codes use config helper.
To get country name by code use data helper


### VERSION 1.5.0
1. Added admin setting with ability to choose API that is going to be used on frontend for storefront
2. To get api type on frontend use $this->helper(Digidirect\StoreLocator\Helper\Config)->getApi(). It returns google by default
3. To get particular API key for each api use Digidirect\StoreLocator\Block\AbstractBlock::getApiKey() method instead of getGoogleApiKey()

### VERSION 1.6.0
1. Added ability to rename store entity. To do it:
   - rename Entity
   - go to Stores -> Configuration -> Store Locator -> Developer Settings and setup your renamed entity as Base Entity.
   After doing this listing (page with all stores on the map) will be working
   - in Stores -> Configuration -> Store Locator -> Developer Settings -> layout settings add new record, choose Renamed Entity and add digidirect_asbstractentity_view_abstractentity_store as handle. It helps avoid renaming layouts in code
    
    
### VERSION 1.6.1
1. Added missed translation in controller
2. Added empty option to "Base entity" dropdown to avoid extra queries
3. Related to previous releases: as abstract entity extension had bug with the same additional handles for set adn entity and it was not noticed until june 2018  your project code probably contains "digidirect_abstractentity_view_set_store" layout file. You need just rename it to "digidirect_abstractentity_view_abstractentity_store"  


### VERSION 1.6.4

Example of adding callback function for attribute:
```
/*
 * Example of adding callback function for attribute
 * Use it in phtml files
 * You need to have access to \Digidirect\Locator\Block\Locator block
 *
 *
 * Result:
 * value of "street" will be like "---Carlingford Rd, 74---"
 */
$block->getChildBlock('storelocator.locator')->setFilterCallback(
    'street',           // any attribute that must be filered
    function($str) {    // callback function
        return '---' . $str . '---';
    }
);
```