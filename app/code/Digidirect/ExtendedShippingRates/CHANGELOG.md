1.0.0
=============
* New features:
    * [#176895](https://digidirect.tpondemand.com/entity/176895) -- Extended Shipping Rates. Solution Architecture
    * [#176894](https://digidirect.tpondemand.com/entity/176894) -- SHIPPING METHOD SETTINGS. As an admin, I want to be able to specify the following parameters for the extension
    * [#176896](https://digidirect.tpondemand.com/entity/176896) -- CART, CHECKOUT PAGE. As a user, I want to be able to view Shipping Methods created by extension on Cart and Checkout pages
    * [#177203](https://digidirect.tpondemand.com/entity/177203) -- SHIPPING RULES SETTINGS. As an admin I want to be able to specify configuration for shipping rules
    * [#177219](https://digidirect.tpondemand.com/entity/177219) -- CARRIERS. As an admin I want to be able to create a carrier and manage it
    * [#177313](https://digidirect.tpondemand.com/entity/177313) -- SHIPPING ZONES. As an admin I want to be able to create shipping zone and manage it
    * [#178696](https://digidirect.tpondemand.com/entity/178696) -- SHIPPING METHODS. As an Admin I want to be able to create new shipping method and manage it
    * [#178816](https://digidirect.tpondemand.com/entity/178816) -- SHIPPING RULES & RESTRICTIONS. As an admin I want to be able to create shipping rules and manage them
    * [#180687](https://digidirect.tpondemand.com/entity/180687) -- SHIPPING RATES CSV UPLOAD. As an admin I want to be able to upload a csv-file with the rates

1.0.1
=============
* Bugfixes:
    * [#187361](https://digidirect.tpondemand.com/entity/187361) -- Impossible to place order with custom shipping method
    * [#187516](https://digidirect.tpondemand.com/entity/187516) -- Impossible to create new Carrier
    * [#187665](https://digidirect.tpondemand.com/entity/187665) -- Project: Pharmacy online. Shipping zone: Impossible to save more than 1 country for a zone
    * [#187693](https://digidirect.tpondemand.com/entity/187693) -- ESR+. Method price is calculated incorrectly for 'Price Per Each Item' setting

1.0.2
=============
* Bugfixes:
    * [#186596](https://digidirect.tpondemand.com/entity/186596) -- It's not possible to delete an entity from its Details page
    * [#186725](https://digidirect.tpondemand.com/entity/186725) -- 'Ship to Specific Countries' setting is ignored
    * [#186756](https://digidirect.tpondemand.com/entity/186756) -- Created At/UpdatedAt values are not correct
    * [#186764](https://digidirect.tpondemand.com/entity/186764) -- Grid contains not correct data after filters were reset
    * [#186774](https://digidirect.tpondemand.com/entity/186774) -- 'Labels' values are not saved
    * [#186854](https://digidirect.tpondemand.com/entity/186854) -- Impossible to create condition for rule
    * [#186965](https://digidirect.tpondemand.com/entity/186965) -- Carrier information is not displayed on edit shipping method page in admin
    * [#188364](https://digidirect.tpondemand.com/entity/188364) -- Error on rule save

1.0.3
=============
* Bugfixes:
    * [#189794](https://digidirect.tpondemand.com/entity/189794) -- ESR. Stack trace is displayed on attempt to open item which doesn't exist

1.0.4
=============
* Bugfixes:
    * [#190628](https://digidirect.tpondemand.com/entity/190628) -- Project: Pharmacy Online. Cart: Shipping rate doesn't change when user changes country on cart page

1.0.5
=============
* Bugfixes:
    * [#203292](https://digidirect.tpondemand.com/entity/203292) -- ESR.'Use Time' setting works incorrectly

1.0.6
=============
* Bugfixes:
    * [#204241](https://digidirect.tpondemand.com/entity/204241) -- all.lego. Checkout: There are no any shipping method available on cart/checkout

1.1.0
=============
* New features:
    * [#241283](https://tp.digidirect.com/241283) -- MASS-UPLOAD. As an admin, I want to be able to upload postcodes/zones for ESR Zones
    * [#241287](https://tp.digidirect.com/241287) -- HIDE METHOD. As an admin, I want to be able to use 'Show Shipping Method' action
    * [#251009](https://tp.digidirect.com/251009) -- RULES CHECK. As a system, I want to be able to check ESR rules after PayPal response
    * [#251255](https://tp.digidirect.com/251255) -- SIMPLE ZONES. As an admin, I want to be able to see reduced number of fields on Zone add/edit page
    * [#251271](https://tp.digidirect.com/251271) -- EXTRA TITLE. As an admin, I want to be able to set additional title for a shipping method
    * [#204045](https://tp.digidirect.com/204045) -- CHANGE TITLE. As an admin I want to be able to specify conditions for shipping method to use alternative title
    * [#251281](https://tp.digidirect.com/251281) -- STATE/COUNTRY ZONES. As an admin, I want Rule to have an ability to use all state/country zones
    * [#253260](https://tp.digidirect.com/253260) -- Zone ID. As an admin, I want to be able to set ID value for a shipping zone
    * [#254168](https://tp.digidirect.com/254168) -- METHOD AVAILABLE. As an admin, I want to be able to Shift Shipping Availability Check
    * [#241285](https://tp.digidirect.com/241285) -- CUSTOM CONDITION. As an admin, I want to be able to use custom conditions
    * [#257566](https://tp.digidirect.com/257566) -- HIDE METHODS. As an admin, I want to be able to hide shipping methods depending on available shipping method

1.1.1
=============
* Improvements:
    * Fix config css file name

1.1.2
=============
* Bugfixes:
    * [#261575](https://digidirect.tpondemand.com/entity/261575) -- [ESR] 'If shipping method <> available' condition must be deleted

1.1.3
=============
* Bugfixes:
    * [#262192](https://digidirect.tpondemand.com/entity/262192) -- [ESR] Admin panel is not user friendly


1.2.0
=============
* New features:
    * [#264581](https://digidirect.tpondemand.com/entity/264581) -- [DISPLAY ALL METHODS] As a customer, I want to be able to see all available shipping methods


1.3.0
=============
* New features:
    * [#268845](https://digidirect.tpondemand.com/entity/268845) -- [ALL PRICES] As a system, I want JSON object to contain all ESR shipping methods prices
    * [#272519](https://digidirect.tpondemand.com/entity/272519) -- [Multiple Delivery Method] As an admin, I want to be able to specify which shipping methods should be hidden if there is a specific shipping method in the cart

1.3.1
=============
* Bugfixes:
    * [#272976](https://digidirect.tpondemand.com/entity/272976) -- [ESR] Custom condition rule is not applied if there is another rule with custom option
    * [#284332](https://digidirect.tpondemand.com/entity/284332) -- [ESR] Warning message after saving shipping rule

1.3.2
=============
* Bugfixes:
    * [#261997](https://digidirect.tpondemand.com/entity/261997) -- [ESR] 'Post Processing' switcher must be deleted

1.3.3
=============
* Bugfixes:
    * [#204748](https://digidirect.tpondemand.com/entity/204748) -- [ESR][Method] Incorrect behavior of 'Packaging weight, value' field

1.3.4
=============
* Bugfixes:
    * [#290462](https://digidirect.tpondemand.com/entity/290462) -- [Project: Digidirect] [ESR] Composer dump-autoload warning

1.3.5
=============
* Bugfixes:
    * [#271617](https://digidirect.tpondemand.com/entity/271617) -- [ESR] Configuration setting "Multiple rates price calculation" is not applied on website level


1.4.0
=============
* New features:
    * [#300266](https://digidirect.tpondemand.com/entity/300266) -- [CONDITION] As an admin, I want new condition to be added

* Bugfixes:
    * [#263254](https://digidirect.tpondemand.com/entity/263254) -- [ESR] Method which matches 2 rules with 'Hide' and 'Show' actioons respectively is displayed on Checkout incorrectly
	
1.4.1	
=============
* Bugfixes:
    * [#302970](https://digidirect.tpondemand.com/entity/302970) -- [RC-249][Checkout - Step 1][Shipping methods] Shipping Methods fail to load and return an error


1.5.0
=============
* New features:
    * [#321442](https://digidirect.tpondemand.com/entity/321442) -- [DISCOUNT CODE] As an admin, I want to be able to use additional condition operators for a discount code
	
1.5.1	
=============
* Bugfixes:
    * [#324215](https://digidirect.tpondemand.com/entity/324215) -- [Extended Shipping Rates] 500 error when click on "Shipping Rules & Restcrictions"
    * [#324473](https://digidirect.tpondemand.com/entity/324473) -- [DHC 2.3.2][Extended Shipping Rates] It's impossible to open Shipping Methods grid in admin panel
	
1.5.2	
=============
* Bugfixes:
    * [#325790](https://digidirect.tpondemand.com/entity/325790) -- [PROJECT: Sportsgirl] Discount amount should not be displayed in order summary after click on "Cancel" button

1.5.3	
=============
* Bugfixes:
    * [#333331](https://digidirect.tpondemand.com/entity/333331) -- [Project: RipCurl][Extended shipping rates] Customer group in shipping rule is changed to default customer groups after changing the status to "Active/Inactive" through mass action


