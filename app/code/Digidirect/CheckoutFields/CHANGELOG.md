1.0.0
=============
* New features:
    * [#144734](https://digidirect.tpondemand.com/entity/144734) -- As an Administrator I want to be able to review additional custom fields on Order review page
    * [#139755](https://digidirect.tpondemand.com/entity/139755) -- As a system, I want all data entered to the additional fields to be stored in a database
    * [#139216](https://digidirect.tpondemand.com/entity/139216) -- As a system, the extension should give possibility to add the following types of the fields (see description)
    * [#139212](https://digidirect.tpondemand.com/entity/139212) -- As a system, the extension should use a configurative XML file
    * [#139124](https://digidirect.tpondemand.com/entity/139124) -- As an Administrator, I want to be able disable/enable additional fields on store view level
    * [#139211](https://digidirect.tpondemand.com/entity/139211) -- As a system I want to have extension gives possibility to setup additional custom fields for checkout page

1.1.0
=============
* Bugfixes:
    * [#170910](https://digidirect.tpondemand.com/entity/170910) -- Additional information os not saved if PayPal payment method is selected
    * [#160145](https://digidirect.tpondemand.com/entity/160145) -- Broken html when at least one field is unchecked
    * [#165440](https://digidirect.tpondemand.com/entity/165440) -- Incorrect calss attribute for checked checkbox in the admin panel
    
* New Features:
    * [#152566](https://digidirect.tpondemand.com/entity/152566) -- ORDER MANAGEMENT. As an admin, I want to be able to manage checkout field in backend of Magento (see description)
    * [#153014](https://digidirect.tpondemand.com/entity/153014) -- XML STRUCTURE. As a system, I want "class" attribute to be added to the xml to allow to apply css class to the field

1.2.0
=============
* New Features:
    * [#183886](https://digidirect.tpondemand.com/entity/183886) -- Custom Checkout fields. Conditions for custom fields in xml. Solution Architecture
    * [#183848](https://digidirect.tpondemand.com/entity/183848) -- As a system, I want to have a possibility to add conditions for custom fields in xml based on attribute value of products

1.2.1
=============
* Bugfixes:
    * [#185876](https://digidirect.tpondemand.com/entity/185876) -- Checkout Fields + C&C. Shipping address block is not hidden on checkout page

1.3.0
==============
* New features:
    * [#199371](https://digidirect.tpondemand.com/entity/199371) -- As an Administrator, I want to be able to hide Custom Checkout field on frontend

* Bugfixes:
    * [#147923](https://digidirect.tpondemand.com/entity/147923) -- Validation messages are not displayed for custom fields if magento required fields are completed
    * [#199833](https://digidirect.tpondemand.com/entity/199833) -- Impossible to place order if no one field is active

1.3.1
===============
* Bugfixes:
    * [#200843](https://digidirect.tpondemand.com/entity/200843) -- Impossible to pass 'Shipping Address' step if Custom fields exist for Billing address step

1.4.0
===============
* New Features:
    * [#196058](https://digidirect.tpondemand.com/entity/196058) -- As a system, I want to be upgraded to ver. 2.2

1.4.1
===============
* New Features:
    * [#196058](https://digidirect.tpondemand.com/entity/196058) -- As a system, I want to be upgraded to ver. 2.2
    
1.4.2
===============
* New Features:
    * [#199881](https://digidirect.tpondemand.com/entity/199881) -- Refactoring to support B2B

1.5.0
===============
* New Features:
    * [#221895](https://digidirect.tpondemand.com/entity/221895) -- As an Administrator, I want to be able to show Custom Checkout field on product detail page

1.5.1
===============
* New Features:
    * [#229365](https://tp.digidirect.com/229365) -- [Project CRV] [Checkout] SQLSTATE[23000] error displays in console during the contract creation

1.5.2
===============
* Improvements:
    * Code improvements

1.5.3
===============
* Bugfixes:
    * [#233091](https://tp.digidirect.com/233091) -- Impossible to pass 'Shipping Address' step if Custom fields exist for Billing address step

1.5.4
===============
* Bugfixes:
    * [#233310](https://tp.digidirect.com/233310) -- Customer_notes value is not attached to an order after place from FE

1.5.5
===============
* Bugfixes:
    * [#233310](https://tp.digidirect.com/233310) -- Customer_notes value is not attached to an order after place from FE

1.5.6
===============
* Bugfixes:
    * [#239768](https://tp.digidirect.com/239768) -- [ORP][Custom Checkout Fields] Email was not sent

1.6.0
===============
* New features:
    * [#265117](https://digidirect.tpondemand.com/entity/265117) -- [ADMIN EDIT] As an Administrator, I want to be able to edit Custom Checkout field in back-office

* Bugfixes:
    * [#265124](https://digidirect.tpondemand.com/entity/265124) -- [CRV project][Admin][Orders] System displays exception in Edit mode for order in Pending status
    * [#265458](https://digidirect.tpondemand.com/entity/265458) -- [Custom Checkout fields] - There is no validation for edit checkout fields on admin panel

1.6.1
=============
* Bugfixes:
    * [#253505](https://digidirect.tpondemand.com/entity/253505) -- [PROJECT CRV][EVENT] Club notes is not added to event report when Payment method = SecurePay

1.6.2
=============
* Bugfixes:
    * [#268699](https://digidirect.tpondemand.com/entity/268699) -- [HOLD] [Project: CRV] [Checkout fields] asynchronous call during order placing
    * [#270147](https://digidirect.tpondemand.com/entity/270147) -- [Project: Ultraceuticals] Exception on attempt to see order details from back-office under certain conditions

1.6.3
=============
* Improvements:
    * [PHP 7.1] As a system, I want the following extensions to be compatible with PHP 7.1

1.6.4
=============
* Improvements:
    * Refactor to use WebAPI

1.7.0
=============
* New features:
    * [#283781](https://digidirect.tpondemand.com/entity/283781) -- As an admin, I wand dependencies between checkout field could be made via XML file
    * [#283571](https://digidirect.tpondemand.com/entity/283571) -- [Product Subscriptions] As an admin, I want to be able to specify fields that should be editable in subscriptions
    * [#284115](https://digidirect.tpondemand.com/entity/284115) -- [Product Subscriptions] As a user, I want new checkout field value to update existing subscription after order placing

1.8.0
=============
* New features:
    * [#288512](https://digidirect.tpondemand.com/entity/288512) -- [CHECKOUT FLAG] As a system, I want to be able to define if user's Outlet doesn't have 'outlet_id' value

1.9.0
=============
* New features:
    * [#283781](https://digidirect.tpondemand.com/entity/283781) -- As an admin, I wand dependencies between checkout field could be made via XML file
    * [#291502](https://digidirect.tpondemand.com/entity/291502) -- [SCRIPT] As a system, I want to be able to update Custom Checkout Fields in Order and Quotes using command line

1.9.0.1
=============
* Bugfixes:
    * [#290531](https://digidirect.tpondemand.com/entity/290531) -- [Project: Neverfail][PO Number][Subscriptions] 'PO Number' and 'To Date' fields should not displayed for residential outlet

2.0.0
=============
* Bugfixes:
    * [#296188](https://digidirect.tpondemand.com/entity/296188) -- [Project: Rip Curl][digidirect_checkoutfields] Custom checkout fields are not stored in database immediately after order placing

2.0.1
=============
* Bugfixes:
    * [#301844](https://digidirect.tpondemand.com/entity/301844) -- [Project RipCurl][OMS] Sync_order error

2.0.2
=============
* Bugfixes:
    * [#297819](https://digidirect.tpondemand.com/entity/297819) -- [Project: DigiDirect] Validation for address mandatory fields is missed on the second Checkout step for logged in user

2.1.0
=============
* New features:
    * [#303189](https://digidirect.tpondemand.com/entity/303189) -- [CUSTOM FIELDS PLACEMENT] As an admin, I want to be able to place custom checkout field in dependence on user state

2.1.1
=============
* Bugfixes:
    * [#303839](https://digidirect.tpondemand.com/entity/303839) -- "There are no commands defined" error in CLI

2.1.2
=============
* Bugfixes:
    * [#297819](https://digidirect.tpondemand.com/entity/297819) -- [Project: DigiDirect] Validation for address mandatory fields is missed on the second Checkout step for logged in user

2.1.3
=============
* Bugfixes:
    * [#307583](https://digidirect.tpondemand.com/entity/307583) -- [Project: DigiDirect] User is not able to proceed to the second Checkout step after proceeding to the first step using browser's arrows
    
