1.0.0
=============
* New features:
    * [#203414](https://digidirect.tpondemand.com/entity/203414) --  As a user, I want to see abstract entity listing page on the frontend
    * [#203415](https://digidirect.tpondemand.com/entity/203415) --  As a user, I want to see abstract entity detail page on the frontend
    * [#205082](https://digidirect.tpondemand.com/entity/205082) --  As an admin, I want to be able to manage abstract entity listing page properties
    * [#205116](https://digidirect.tpondemand.com/entity/205116) --  As an admin, I want to be able to manage Longitude and Latitude for the store
    * [#205122](https://digidirect.tpondemand.com/entity/205122) --  As an admin, I want default abstract entity to be created for the extension, the entity will have the following attributes
    * [#207577](https://digidirect.tpondemand.com/entity/207577) --  As a user, I want to be able to define directions to the store on the webpage
    
1.1.0
===============
    
* New features:
    * [#205082](https://digidirect.tpondemand.com/entity/205082) -- As an admin, I want to be able to manage abstract entity listing page properties

* Bugfixes:
    * [#220243](https://digidirect.tpondemand.com/entity/220243) -- 500 for Listing and Detail page

1.2.0
================        
* New features:
    * [#205122](https://digidirect.tpondemand.com/entity/205122) -- As an admin, I want default abstract entity to be created for the extension, the entity will have the following attributes    
    
* Bugfixes:
    *[#225851](https://digidirect.tpondemand.com/entity/225851) -- Directions: "At least one of the origin, destination, or waypoints could not be geocoded." message is not removed after directions have been built successfully
    
1.3.0
================
* New features:
    * [#207577](https://digidirect.tpondemand.com/entity/207577) -- As a user, I want to be able to define directions to the store on the webpage
    * [#224541](https://digidirect.tpondemand.com/entity/224541) -- As an user I want to be able to choose my state and see prefiltered stores on the map    
    
1.3.1    
================
* Bugfixes:
    * [#237448](https://digidirect.tpondemand.com/entity/237448) -- PDP: incorrect showrooms are shown for SA, WA states

1.4.0    
================
* New features:
    * [#260100](https://digidirect.tpondemand.com/entity/260100) -- As an admin, I want to be able to specify if a detail page should be displayed on a pop-up
    * [#205082](https://digidirect.tpondemand.com/entity/205082) -- As an admin, I want to be able to manage abstract entity listing page properties


* Bugfixes:
    * [#260208](https://digidirect.tpondemand.com/entity/260208) -- Fatal error on the locator listing page on FE    
    
    
1.4.1
=================

* New features:
    * [#261219](https://digidirect.tpondemand.com/entity/261219) -- As a system, I want to be able to filter Abstract Entities on the Locator by 'country' attribute
    
    
1.5.0
==================
* New features:
    * [#259931](https://digidirect.tpondemand.com/entity/259931) -- As an admin, I want to be able to specify which maps API the locator should use

* Bugfixes:
    * [#261987](https://digidirect.tpondemand.com/entity/261987) -- MySQL error for empty searchTerm    


1.6.0
===================
    
* [#265605](https://digidirect.tpondemand.com/entity/265605) -- As an admin, I want to be able to specify layout code for an Abstract Entity    

1.6.1
==================
* Bugfixes:
    * [#266293](https://digidirect.tpondemand.com/entity/266293) -- Please translate store page content
    
1.6.2
==================    
* New features:
    * [#258911](https://digidirect.tpondemand.com/entity/258911) -- As a user, I want to see abstract entity detail page on the frontend    

1.6.3
===================
* Bugfixes:
    * [#268230](https://digidirect.tpondemand.com/entity/268230) -- Search doesn't return results from multiple entities
    
1.6.4
===================
 * [#262197](https://digidirect.tpondemand.com/entity/262197) -- HTML textarea attributes does not convert image urls

1.6.5
=============
* Improvements:
    * [PHP 7.1] As a system, I want the following extensions to be compatible with PHP 7.1

1.7.0	
=============
* New features:
    * [#281908](https://digidirect.tpondemand.com/entity/281908) -- [CHILD ENTITIES] As a customer, I want to be able to view entity's Parent option on a locator

* Bugfixes:
    * [#282275](https://digidirect.tpondemand.com/entity/282275) -- [VANILLADEMO][Store Locator] Search results are not displayed for store locator
	
1.8.0
============
* New features:
    * [#291306](https://digidirect.tpondemand.com/entity/291306) -- As an admin, I want set up Length Unit to be used in Locator module

* Bugfixes:
    * [#285419](https://digidirect.tpondemand.com/entity/285419) -- [Locator][Click & Collect] Stores are not displayed on the 'Find collect store place' page
    * [#285472](https://digidirect.tpondemand.com/entity/285472) -- [Project: Cokevending][Storelocator]There is an error during di:compile - Extra parameters passed to parent construct
    * [#291989](https://digidirect.tpondemand.com/entity/291989) -- There is double converting from miles to the kilometers in case 'Length Unit' = 'Miles'

1.9.0
=============
* New features:
    * [#297705](https://digidirect.tpondemand.com/entity/297705) -- [Locator][Click & Collect] As a system, I want availability flag to be added
    	
1.9.0.1
==============
* Improvements:
    * Added ability to remove trailing slash
    * Url keys are being processed correctly
    * Added ability to display stores in all countries(Previously they were displayed in a default country)
    * Added ability to skip search term processing
    * Added ability to debug queries
    * Added ability to process postcodes queries correctly
    * Reindex fix moved from recent major update to this minor and vice versa
=======

1.10.0
* New features:
    * [#300771](https://digidirect.tpondemand.com/entity/300771) -- [DISTANCE TO STORES] As a customer, I want to be able to view the distance from my geo location for all stores in the list

1.10.1
============
* Bugfixes:
    * [#303555](https://digidirect.tpondemand.com/entity/303555) -- [Project: DigiDirect] Searching by postcode always returns the US even when it is not set up in admin	
	
1.10.2
============
* Bugfixes:
    * [#308439](https://digidirect.tpondemand.com/entity/308439) -- [Project: DigiDirect] Store entity is not searchable on the "Store locator" page after its creating	
    	
1.11.0
==============
* New features:
    * [#316440](https://digidirect.tpondemand.com/entity/316440) -- [DEFAULT COUNTRY SEARCH] As a user, I want to see address suggestions only for the default country while searching    	
    
1.12.0
==============
* New features:
    * [#318222](https://digidirect.tpondemand.com/entity/318222) -- [RADIUS RESULTS] As a customer, I want to view results in radius based on google autocomplete suggestion only
    
* Bugfixes:
    * [#319344](https://digidirect.tpondemand.com/entity/319344) -- There are not stores in the search result if "Using Radius" mode and Extend the Radius
    * [#319490](https://digidirect.tpondemand.com/entity/319490) -- [Locator] Stores are displayed in the incorrect order in case select by distance is selected  
    	
