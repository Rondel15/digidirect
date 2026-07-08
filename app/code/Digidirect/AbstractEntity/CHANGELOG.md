1.0.0
=============
* Solution Architecture:
    * [#201358](https://digidirect.tpondemand.com/entity/201358) -- Abstract Entity. Solution Architecture
* New features:
    * [#201562](https://digidirect.tpondemand.com/entity/201562) -- ABSTRACT ENTITY LISTING. As an admin, I want to see <new entity> listing page, so that I can find new entity easier
    * [#201548](https://digidirect.tpondemand.com/entity/201548) -- ABSTRACT ENTITY DETAILS. As an admin, I want to be able to create/edit/delete new ABSTRACT entity
    * [#201356](https://digidirect.tpondemand.com/entity/201356) -- OPTION LISTING. As an admin, I want to see <new entity> listing page, so that I can find new entity easier
    * [#201547](https://digidirect.tpondemand.com/entity/201547) -- OPTION DETAILS. As an admin, I want to be able to create/edit/delete new entity
    * [#201581](https://digidirect.tpondemand.com/entity/201581) -- ROLES&PERMISSIONS. As an admin, I want to be able to manage access for new entities

1.1.0
=============
* New features:
    * [#199184](https://digidirect.tpondemand.com/entity/199184) -- [VISIBLE ON FRONTEND] As an admin I want to be able to specify if an AE is visible on frontend
* Bugfixes:
    * [#206510](https://digidirect.tpondemand.com/entity/206510) -- [Abstract Entity] The URL Key field accepts spaces

1.2.0
=============
* Improvements:
    * Improvement: Add Entity Name method to the AE model

1.3.0
=============
* New features:
    * [#205388](https://digidirect.tpondemand.com/entity/205388) -- [ENTITY RELATION] As an admin, I want Abstract Entity to have possibility to be related to another Entity's values
* Improvements:
    * Improvement: Magento 2.2 Compatibility

1.4.0
=============
* New features:
    * [#215953](https://digidirect.tpondemand.com/entity/215953) -- [ENTITY OPTION RELATION] As an admin, I want Abstract Entity's attribute to be related to another Entity's option

2.0.0
=============
* Improvements:
    * Improvement: Magento 2.2 Compatibility
* New features:
    * [#201893](https://digidirect.tpondemand.com/entity/201893) -- [INDEX] As a system, I want indexes to be added for abstract entities (this will allow working with big data)
    * [#218084](https://digidirect.tpondemand.com/entity/218084) -- [DETAIL PAGE] As a user, I want to see abstract entity detail page on the frontend
* Bugfixes:
    * [#218566](https://digidirect.tpondemand.com/entity/218566) -- [PROJECT: TAF][ABSTRACT ENTITY] Error message on updating stores

2.1.0
=============
* New features:
    * [#219689](https://digidirect.tpondemand.com/entity/219689) -- As an extension, I should be able to work on Magento 2.1.8    
    
2.2.0
=============
* New features:
    * [#215245](https://digidirect.tpondemand.com/entity/215245) -- As a user, I want to see listing page, so that I can see full list of options
    * [#219495](https://digidirect.tpondemand.com/entity/219495) -- As an admin, I want to be able to manage module properties

* Bugfixes:
    * [#214993](https://digidirect.tpondemand.com/entity/214993) -- The Store image attaches as a placeholder in back-office
    * [#221829](https://digidirect.tpondemand.com/entity/221829) -- Abstract Entity Index: Reindex is executed when Enable Table Index = No in admin panel

2.3.0
=============
* New features:
    * [#223197](https://digidirect.tpondemand.com/entity/223197) -- After saving changes of entity no reindex warning is displayed and Correspondent Cache status is not changed    

2.4.0
=============
* New features:
    * [#225124](https://digidirect.tpondemand.com/entity/225124) -- [WYSWIG OPTION TYPE] As a developer, I want to be able to create option of the entity with WYSWIG entity
* Bugfixes:
    * [#225122](https://digidirect.tpondemand.com/entity/225122) -- [Voucher] It's impossible to change Entity's Attribute Credentials in Configuration

2.5.0
=============
* New features:
    * [#224441](https://digidirect.tpondemand.com/entity/224441) -- [ENTITY OPTIONS RELATION] As an admin I want to be able to create a multiselect attribute which will be related to another Entity’s options
    
2.6.0
=============
* New features:
    * [#219495](https://digidirect.tpondemand.com/entity/219495) -- As an admin, I want to be able to manage module properties    

2.6.1
=============
* Improvements:
    * New Source Model added. Ability to get all entities with an empty option.

2.6.2
=============
* Bugfixes:
    * [#232775](https://digidirect.tpondemand.com/entity/232775) -- My Store Widget: Change engine to innodb
    * [#235391](https://digidirect.tpondemand.com/entity/235391) -- [Project: Ultraceuticals] Error during reindex if abstact entity has more than 30 attributes
    * [#235537](https://digidirect.tpondemand.com/entity/235537) -- [Project: Ultraceuticals] Extension configurations: Allow fields with decimal values to be selected as Referral Amount

2.6.3
=============
* Improvements:
    New event `adminhtml_digidirect_abstractentity_save_after` added.
    
2.6.4
=============
* Improvements:
    Added ability to configure search type(in code)
    
2.7.0
=============
* New features:
    * [#238995](https://digidirect.tpondemand.com/entity/238995) -- [REST API] As an admin, I want Abstract Entity module to have a REST API call to get the AE’s data by ID

* Bugfixes:
    * [#238587](https://digidirect.tpondemand.com/entity/238587) -- [Project Nick Scali] [UAT] PDP: all showrooms are show for simple and configurable available for selected simple products

2.8.0
=============
* New features:
    * [#250555](https://digidirect.tpondemand.com/entity/250555) -- [GET ABSTRACT ENTITIES BY IDS] As a system, I want helper method to get entity options by multiple IDs to be implemented
    * [#250554](https://digidirect.tpondemand.com/entity/250554) -- [SOURCE MODEL] As a system, I want Abstract Entity Source Model class to be implemented

2.8.1
=============
* Bugfixes:
    * [#257454](https://digidirect.tpondemand.com/entity/257454) -- [Project: Neverfail] [Abstract Entity]External Invoices] Magento error is displayed after saving an invoice updated via integration

2.9.0
=============
* Improvements:
    * Use searchable dropdown for finding parent entity on the AE editing page.
* Bugfixes:
    * [#254666](https://digidirect.tpondemand.com/entity/254666) -- [Project: Neverfail] [Abstract Entity]: Invoice Line: Fatal error on 'Invoice Line' details page in the admin panel

2.9.1
=============
* Bugfixes:
    * [#260208](https://digidirect.tpondemand.com/entity/260208) -- [Store locator] Fatal error on the locator listing page on FE
    * [#260255](https://digidirect.tpondemand.com/entity/260255) -- [Abstract Entity]: "Parent entity" attribute displays as required, cannot save new entity without it

2.10.0
=============
* New features:
    * [#216755](https://digidirect.tpondemand.com/entity/216755) -- [ADD ATTRIBUTE] As an admin, I want to be able to add/edit Abstract Entity's attribute

2.11.0
=============
* Improvements:
    * Ability to get Abstract Entity collection by Entity Name in templates

3.0.0
=============
* Bugfixes:
    * [#265528](https://digidirect.tpondemand.com/entity/265528) -- Abstract Entity information not display store view content

3.1.0
=============
* New features:
    * [#265742](https://digidirect.tpondemand.com/entity/265742) -- As a user, I want to be able to load next batch of abstract entity options on Abstract Entity Option Listing Page without page re-load
* Bugfixes:
    * [#271991](https://digidirect.tpondemand.com/entity/271991) -- [Project: Trybe] [ABSTRACT ENTITY] SVG files couldn't be added to Abstract Entity

3.1.1
=============
* Improvements:
    * [PHP 7.1] As a system, I want the following extensions to be compatible with PHP 7.1

3.1.2
=============
* Bugfixes:
    * [#224343](https://digidirect.tpondemand.com/entity/224343) -- AE: Option list: Wrong qty in the "show per page" dropdown
    * [#287604](https://digidirect.tpondemand.com/entity/287604) -- [Project: Neverfail]External Invoice Settings not visible in Backend

3.1.3
=============
* Bugfixes:
    * [#292152](https://digidirect.tpondemand.com/entity/292152) -- [Project: Rip Curl][Back-office][Abstract Entity Attributes] Added options are not saved for the attribute with input type 'drop-down list'

3.1.4
=============
* Bugfixes:
    * [#296393](https://digidirect.tpondemand.com/entity/296393) -- backend_model is empty in case attribute input type is multiselect

3.1.5
=============
* Bugfixes:
    * [#297580](https://digidirect.tpondemand.com/entity/297580) -- [Magento 2.3.1] Abstract entity cannot be deleted
    * [#300827](https://digidirect.tpondemand.com/entity/300827) -- [Abstract Entity] Error during compilation

3.2.0
=============
* New features:
    * [#302037](https://digidirect.tpondemand.com/entity/302037) -- POST: /V1/abstractentity/type/:attributeSetName
    * [#302038](https://digidirect.tpondemand.com/entity/302038) -- PUT: /V1/abstractentity/type/:attributeSetName/:id
    * [#302039](https://digidirect.tpondemand.com/entity/302039) -- GET: /V1/abstractentity/type/:attributeSetName
    * [#302042](https://digidirect.tpondemand.com/entity/302042) -- DELETE: /V1/abstractentity/type/:attributeSetName/:id

3.2.1
=============
* Bugfixes:
    * [#305183](https://digidirect.tpondemand.com/entity/305183) -- [Abstract Entity] It is possible to create duplicates for existing entity

3.2.2
=============
* Bugfixes:
    * [#313056](https://digidirect.tpondemand.com/entity/313056) -- repository tries to use index table in admin area and generated _index_0 table name, but this table doesn't exist (index is not generated for default store).

3.3.0
=============
* New features:
    * [#313112](https://digidirect.tpondemand.com/entity/313112) -- [Converse] - [Store Locator] The pattern described should be configurable

3.4.0
=============
* Improvements:
    * Back-Office data validation pool

3.4.1
=============
* Bugfixes:
    * [#334708](https://digidirect.tpondemand.com/entity/334708) -- [Project: NEVERFAIL] [abstractentity] optimize getSize/getItems method

3.4.2
==============
* Bugfixes:
    * [#343777](https://digidirect.tpondemand.com/entity/343777) -- [Ultra 2.3.3] Reindex does not work correctly

3.4.3
==============
* Bugfixes:
    * [#343310](https://digidirect.tpondemand.com/entity/343310) -- [Project: Neverfail] Cant insert widget into "Text area"

