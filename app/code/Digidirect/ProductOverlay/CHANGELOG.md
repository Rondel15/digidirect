1.0.0
=============
* Solution Architecture:
    * [#129157](https://digidirect.tpondemand.com/entity/129157) -- Product Overlay. Solution Architecture
* New features:
    * [#129231](https://digidirect.tpondemand.com/entity/129231) -- DISPLAY SETTINGS. As an admin, I want to specify DOM-selectors for containers of the overlay
    * [#129232](https://digidirect.tpondemand.com/entity/129232) -- ON SALE CONDITION. As an admin, I want to be able to set up "On Sale Condition"
    * [#129233](https://digidirect.tpondemand.com/entity/129233) -- IS NEW CONDITION. As an admin, I want to be able to set up "Is New Condition"
    * [#129239](https://digidirect.tpondemand.com/entity/129239) -- MANAGE OVERLAY. As an admin, I want to be able to manage overlay for the product
    * [#130110](https://digidirect.tpondemand.com/entity/130110) -- MIME TYPES. As an admin, I want to be able to specify image types and MIME for product overlays
    * [#129587](https://digidirect.tpondemand.com/entity/129587) -- OVERLAY CONDITIONS. As an admin, I want to be able to specify conditions for overlay to be displayed
    * [#129240](https://digidirect.tpondemand.com/entity/129240) -- FRONTEND. As a user, I want to see product overlays on the store pages: product detail page / product listing page / other (see description)
    * [#151419](https://digidirect.tpondemand.com/entity/151419) -- STOCK CONDITIONS. As an admin, I want to be able to specify stock conditions for the label (see description)
    * [#161332](https://digidirect.tpondemand.com/entity/161332) -- R7.CACHE. As an admin, I want to see cache invalid message once overlay created or updated
    * [#164357](https://digidirect.tpondemand.com/entity/164357) -- R7. As am admin, I want to be able to add an overlay to the product as a private sale (see description)
   
1.0.1
=============
* Bug fixes
    * [#178679](https://digidirect.tpondemand.com/entity/178679) -- Error on di compilation
    * [#178973](https://digidirect.tpondemand.com/entity/178973) -- Overlays: After installing overlay module attributes "Product is New from date" and "Product is New to date" didn't create  
    * [#181450](https://digidirect.tpondemand.com/entity/181450) -- Product Overlay: An overlay is enlarged after resizing browser's window

1.1.0
=============
* New features:
    * [#151418](https://digidirect.tpondemand.com/entity/151418) -- STOCK LABEL FOR OVERLAY. As an admin, I want to be able to specify stock label for the overlay (see description)
    * [#179641](https://digidirect.tpondemand.com/entity/179641) -- STOCK LABEL FRONTEND. As a user, I want to see stock label on the product detail page as an overlay (see description)
* Bug fixes
    * [#181450](https://digidirect.tpondemand.com/entity/181450) -- Product Overlay. An overlay is enlarged after resizing browser's window

1.1.1
=============
* Bug fixes
    * [#183889](https://digidirect.tpondemand.com/entity/183889) -- Fatal error on search result page
    * [#183912](https://digidirect.tpondemand.com/entity/183912) -- Overlays for general customer group is not displays for logged in general user, but displays for guest

1.1.2
=============
* Bug fixes
    * [#184809](https://digidirect.tpondemand.com/entity/184809) -- On Sale: Overlay isn't displayed for product if it matches the "Min Discount Amount" condition and doesn't match the "Min Discount Percentage" condition
    * [#184861](https://digidirect.tpondemand.com/entity/184861) -- An overlay is displayed for the particular store if the "Category Page Overlay Container" and "Product Page Overlay Container" fields are empty for this store

1.1.3
=============
* Bug fixes
    * [#161329](https://digidirect.tpondemand.com/entity/161329) -- Product Overlay. Overlays are displayed for products on Cart page
    * [#186306](https://digidirect.tpondemand.com/entity/186306) -- PROJECT: Linfox. Product Overlay - Improper image size used if it is specified only for one of the pages
    * [#186432](https://digidirect.tpondemand.com/entity/186432) -- Project: Pharmacy Online. An error occurs in the log: The element 'category.product.type.details.renderers' already has a child with alias 'configurable'

1.1.4
=============
* Bugfixes:
    * [#161121](https://digidirect.tpondemand.com/entity/161121) -- [Product Overlay] The overlay is displayed on the page even if specified selector is not found
    * [#163779](https://digidirect.tpondemand.com/entity/163779) -- [Overlay Condition] An Overlay is displayed for all simple products of the configurable one even if only one of them match the overlay conditions
    * [#190966](https://digidirect.tpondemand.com/entity/190966) -- [Product Overlay] An overlay is displayed for a product in a widget even if "Use For Parent" = "No"
    * [#196001](https://digidirect.tpondemand.com/entity/196001) -- [Product Overlay] An error occurs in WebAPI module due to the extension

1.1.5
=============
* Improvements:
    * Improve JS-logic of the Configurable Swatches rendering.

1.2.0
=============
* New features:
    * [#204185](https://digidirect.tpondemand.com/entity/204185) -- GENERAL ASSIGNMENT SETTING. As an admin, I want to be able to specify if assignment per product is enabled for the product
    * [#204183](https://digidirect.tpondemand.com/entity/204183) -- OVERLAY PER PRODUCT. As an admin, I want to be able to assign overlay to product on the product detail page

2.0.0
=============
* Improvements:
    * Magento 2.2 Compatibility
* Bugfixes:
    * [#216194](https://digidirect.tpondemand.com/entity/216194) -- [PROJECT: TAF][Overlay][Back-office] Impossible to Edit overlay: (InvalidArgumentException): Unable to unserialize value.

2.0.1
=============
* Bugfixes:
    * [#226496](https://digidirect.tpondemand.com/entity/226496) -- Improve performance

2.0.2
=============
* Bugfixes:
    * [#226496](https://digidirect.tpondemand.com/entity/226496) -- Improve performance

2.0.3
=============
* Bugfixes:
    * [#229123](https://digidirect.tpondemand.com/entity/229123) -- [Product Overlay] Overlay with Use for Parent =NO is not displayed for simple product
    * [#229139](https://digidirect.tpondemand.com/entity/229139) -- [Product Overlay] Overlay with "Price Range" condition is displayed incorrectly for configurable product
    
2.0.4
==============
* Bugfixes:
    * [#254200](https://digidirect.tpondemand.com/entity/254200) -- There is Null in info block for Configurable Product    

2.0.5
==============
* Bugfixes:
    * [#261666](https://digidirect.tpondemand.com/entity/261666) -- Product overlay is displayed for all products when they don't match the overlay conditions    

2.1.0
==============
* New features:
    * [#264578](https://digidirect.tpondemand.com/entity/264578) -- [RECENTLY VIEWED BLOCK] As a customer, I want to be able to use Overlays functionality in 'Recently Viewed' block
* Bugfixes:
    * [#261985](https://digidirect.tpondemand.com/entity/261985) -- [Project: AUSPost][Product's overlay] An overlay is not displayed for the config sub-item when it matches the overlay's condition
    * [#266806](https://digidirect.tpondemand.com/entity/266806) -- [Product Overlay] Store view scope is not working for overlays

2.1.1
===============
* Bugfixes:
    * [#268103](https://digidirect.tpondemand.com/entity/268103) -- "Sale" overlay is displayed on both: FIXED VALUE and RELOADABLE

2.1.2
===============
* Improvements:
    * Refactor JS

2.1.3
===============
* Bugfixes:
    * [#268103](https://digidirect.tpondemand.com/entity/268103) -- [Product Overlay] Product Overlay Rule isn't applied when overlay on product level is set and disabled

2.1.4
===============
* Bugfixes:
    * [#284341](https://digidirect.tpondemand.com/entity/284341) -- [M2.3][Product Overlays] Overlays are not shown on PLP

2.1.5
===============
* Bugfixes:
    * [#266407](https://digidirect.tpondemand.com/entity/266407) -- [Product Overlay][Frontend] Clean the cash doesn't help to apply changes

2.2.0
==============
* New features:
    * [#286981](https://digidirect.tpondemand.com/entity/286981) -- As an admin, I want to be able to specify a Product Overlay for a Catalog Price Rule

2.2.1
===============
* Bugfixes:
    * [#297285](https://digidirect.tpondemand.com/entity/297285) -- [M2.3.1][Product Overlays] Overlays are not shown on PLP

2.2.2
===============
* Bugfixes:
    * [#310896](https://digidirect.tpondemand.com/entity/310896) -- [Overlays][Project: digiDirect] "From Date" saved value goes foward up to 24h on save

2.3.0
===============
* Bugfixes:
    * [#289205](https://digidirect.tpondemand.com/entity/289205) -- [RELATED PRICE RULE] As an admin, I want related Overlay to be disabled on CPR delete

2.3.1
===============
* Bugfixes:
    * [#313981](https://digidirect.tpondemand.com/entity/313981) -- [Project: DigiDirect] GMT timezone is used in Date Range settings on Conditions tab
    * [#314371](https://digidirect.tpondemand.com/entity/314371) -- [Project: DigiDirect] Time not display, after saved, in the tab "Conditions" if enter value 00:00
    * [#314386](https://digidirect.tpondemand.com/entity/314386) -- [Project: Digidirect] Error when enter not valid value time in the line "From Time" and "To time"
    * [#314784](https://digidirect.tpondemand.com/entity/314784) -- [Project: DigiDirect] Date change after save

2.4.0
==============
* New features:
    * [#316942](https://digidirect.tpondemand.com/entity/316942) -- [CACHE CLEANER] As an admin, I want to be able to enable smart cache cleaner and it's schedule

2.4.1
===============
* Bugfixes:
    * [#316293](https://digidirect.tpondemand.com/entity/316293) -- [Project: DigiDirect] GMT timezone is used in Date Range settings on Conditions tab

2.4.2
===============
* Bugfixes:
    * [#314868](https://digidirect.tpondemand.com/entity/314868) -- [Project: DigiDirect] The time is changed after save if enter value time with "-"

2.5.0
===============
* New features:
    * [#314766](https://digidirect.tpondemand.com/entity/314766) -- [ONE DIV FOR CONF] As a customer, I want only one overlay to be loaded for a configurable product

2.5.1
===============
* Bugfixes:
    * [#317640](https://digidirect.tpondemand.com/entity/317640) -- [Project: DigiDirect] Conditions for the overlay could not be set up once it was assigned and then unassigned to the catalog price rule

2.5.2
===============
* Bugfixes:
    * [#315859](https://digidirect.tpondemand.com/entity/315859) -- [Project: Motic] Overlays icon can not be shown at cross sell product in cart page

2.5.3
===============
* Bugfixes:
    * [#327111](https://digidirect.tpondemand.com/entity/327111) -- [Motic Project][Overlay] Just One Overlay is Displayed for Product

2.5.4
===============
* Bugfixes:
    * [#326545](https://digidirect.tpondemand.com/entity/326545) -- [Project: Motic][Grouped] Overlay is applied to parent product when option "Use for parent" is set to NO

2.6.0
===============
* Bugfixes:
    * [#330709](https://digidirect.tpondemand.com/entity/330709) -- [MISSED CRON JOBS] As a system, I want missed cron jobs to be decreased by puting them into a separate cron group
