1.0.0
=============
* New features:
    * [#146070](https://digidirect.tpondemand.com/entity/146070) -- SHIPPING METHOD SETTINGS. As an admin I want a new shipping method "Click & Collect" to be created
    * [#152294](https://digidirect.tpondemand.com/entity/152294) -- PRODUCT DETAIL. As a user, I want to see click and collect link on product detail page
    * [#152295](https://digidirect.tpondemand.com/entity/152295) -- CART. As a user, I want to see click and collect link on cart page
    * [#152296](https://digidirect.tpondemand.com/entity/152296) -- CHECKOUT. As a user, I want the Checkout page with Click & Collect items to function as described
    * [#154866](https://digidirect.tpondemand.com/entity/154866) -- EDIT CART PRODUCT. As a user, I want to see store selected on the product detail page when I edit product from cart page (see description)
    * [#171586](https://digidirect.tpondemand.com/entity/171586) -- DEFAULT ADDRESS. As an admin, I want to be able to enter default address for click & collect shipping method, the address will be used as default user address on checkout if address is not required


1.0.1
=============
* Bugfixes:
    * [#176842](https://digidirect.tpondemand.com/entity/176842) -- MAGENTO BUG. PROJECT: Platypus. User can't place order but can add item to cart

1.0.2
=============
* Bugfixes:
    * [#180258](https://digidirect.tpondemand.com/entity/180258) -- PROJECT: Platypus. Checkout does not work if 'Click and Collect' is disabled

1.0.3
=============
* Bugfixes:
    * [#183823](https://digidirect.tpondemand.com/entity/183823) -- PROJECT: Platypus. C&C "regionId is a required field" error for New Zealand

1.0.4
=============
* Bugfixes:
    * [#184789](https://digidirect.tpondemand.com/entity/184789) -- PROJECT: Platypus. Address autocomplete + C&C: Dummy shipping address is not used when admin chooses "Enable Suburb&Postcode Autocomplete"


1.2.0
=============
* New features:
    * [#171416](https://digidirect.tpondemand.com/entity/171416) -- CART: SINGLE PICKUP LOCATION. As a User I want to view Click & Collect: Single Pickup Location shipping method on Cart
    * [#171420](https://digidirect.tpondemand.com/entity/171420) -- CHECKOUT: SINGLE PICKUP LOCATION. As a User I want to view Click & Collect: Single Pickup Location shipping method on Checkout
    * [#171449](https://digidirect.tpondemand.com/entity/171449) -- SHIPPING METHOD VARIATIONS SETTINGS. As an admin I want a variation of a Click & Collect shipping method to be created
    * [#173581](https://digidirect.tpondemand.com/entity/173581) -- PICKUP ADDRESS FORMAT. As an admin I want to be able to specify the format of pickup address which is visible on Frontend
    * [#180845](https://digidirect.tpondemand.com/entity/180845) -- PAYPAL REVIEW. As a user I want "Shipping Address" fields to be hidden on PayPal Review page if I select Click & Collect shipping method


2.0.0
=============
* New features:
    * [#180845](https://digidirect.tpondemand.com/entity/180845) -- PAYPAL REVIEW. As a user I want "Shipping Address" fields to be hidden on PayPal Review page if I select Click & Collect shipping method

* Bugfixes:
    * [#184333](https://digidirect.tpondemand.com/entity/184333) -- C&C. 'Next' button on checkout page is not clickable
    * [#184821](https://digidirect.tpondemand.com/entity/184821) -- PROJECT: Pharmacy. Click&Collect: Order is not placed with Paypal (guest)
    * [#185876](https://digidirect.tpondemand.com/entity/185876) -- Checkout Fields + C&C: Shipping address block is not hidden on checkout page
    * [#185899](https://digidirect.tpondemand.com/entity/185899) -- PROJECT: Platypus. Virtual product has delivery type

2.0.1
=============
* Bugfixes:
    * [#186730](https://digidirect.tpondemand.com/entity/186730) -- PROJECT: Platypus. Where can I try this: NZ postcode is displayed on the map near Africa if there were no stores found


2.1.0
=============
* New features:
    * [#187724](https://digidirect.tpondemand.com/entity/187724) -- APPLICABLE COUNTRIES. As an admin I want to be able to specify countries for which Click & Collect shipping method will be available
    * [#187740](https://digidirect.tpondemand.com/entity/187740) -- SELECT COUNTRY ON CART AND CHECKOUT. As a user I want Click and Collect shipping method to be available only for specific countries

* Bugfixes:
    * [#187079](https://digidirect.tpondemand.com/entity/187079) -- PROJECT: Platypus. C&C Checkout: Logged in user cannot edit billing address for C&C order
    * [#187275](https://digidirect.tpondemand.com/entity/187275) -- PROJECT: Pharmacy online. Checkout step 2: Click & Collect: After click cancel for address form user cannot enter address again
    * [#187279](https://digidirect.tpondemand.com/entity/187279) -- PROJECT: Pharmacy Online. Click & Collect: Billing information is settled in quote with shipping data if collect selected as shipping method
    * [#187338](https://digidirect.tpondemand.com/entity/187338) -- PROJECT: Pharmacy Online. Checkout C&C: There is NO edit link for billing address
    * [#187348](https://digidirect.tpondemand.com/entity/187348) -- PROJECT: Pharmacy Online. Billing address saved, but address form displays when user comes to checkout
    * [#187842](https://digidirect.tpondemand.com/entity/187348) -- C&C. Console error, impossible to place order in some cases

2.1.1
=============
* Bugfixes:
    * [#187279](https://digidirect.tpondemand.com/entity/187279) -- PROJECT: Pharmacy Online. Click & Collect: Billing information is settled in quote with shipping data if collect selected as shipping method

2.1.2
=============
* Bugfixes:
    * [#188181](https://digidirect.tpondemand.com/entity/188181) -- The "No such entity with cartId" error message appears if admin tries to create an order via admin panel

2.1.3
=============
* Bugfixes:
    * [#191068](https://digidirect.tpondemand.com/entity/191068) -- PROJECT: Platypus. C&C on Cart. JS error occurs when customer enters invalid postcode in 'Find a store' pop up


2.2.0
=============
* New features:
    * [#218040](https://digidirect.tpondemand.com/entity/218040) -- [CHECKOUT] As a user, I want to be able to change my shipping option on Checkout

* Bugfixes:
    * [#220731](https://digidirect.tpondemand.com/entity/220731) -- [CC]: Checkout: Order summary block contains the store name
    * [#220750](https://digidirect.tpondemand.com/entity/220750) -- [CC]: Back-office: There are no information about store on order details page

2.2.1
=============
* Bugfixes:
    * [#226390](https://digidirect.tpondemand.com/entity/226390) -- [PROJECT: TAF][Click&Collect][PayPal] Billing Address is displaying code in the Admin
    

2.3.0
=============
* New features:
    * [#219343](https://digidirect.tpondemand.com/entity/219343) -- [Order View Page] As an admin I want to see Store id and Address Details on Order View Page

2.3.1
=============
* Bugfixes:
    * [#220732](https://digidirect.tpondemand.com/entity/220732) -- [CC]: Cart: There is NO information if C&C method and store selected
    * [#220755](https://digidirect.tpondemand.com/entity/220755) -- [CC]: Cart: Store name is NOT updated after changing
    * [#221245](https://digidirect.tpondemand.com/entity/221245) -- [CC]: PDP: When user choose radio-button for CD shipping method, CC search form should be hidden

2.3.2
=============
* Bugfixes:
    * [#229970](https://tp.digidirect.com/229970) -- [PROJECT: TAF][Afterpay][PROD] Orders are absent in the Admin (Afterpay)

2.3.3
=============
* Bugfixes:
    * [#231305](https://tp.digidirect.com/231305) -- [PROJECT: TAF][Collect] Order cannot be opened from the back-end

2.3.4
=============
* Bugfixes:
    * [#229503](https://tp.digidirect.com/229503) -- [CC]: Cart: Radio buttons for Shipping and Tax stayed after C&C method has been applied

2.3.5
=============
* Improvements
    * Code improvements


2.4.0
=============
* New features:
    * [#231748](https://tp.digidirect.com/231748) -- [SHIPPING METHOD SETTINGS] As an admin, I want to be able to set product's add to cart restriction for single store
    * [#231754](https://tp.digidirect.com/231754) -- [ONE STORE IN CART] As a customer, I want to be able to add to cart products available in one store only
    * [#232207](https://tp.digidirect.com/232207) -- [DELIVERY IN CART] As a customer, I want to be able to use only delivery option if there is a delivery product in the cart


2.5.0
=============
* New features:
    * [#254282](https://tp.digidirect.com/254282) -- [AVAILABILITY CHECK C&C] As a system, I want to use Shipping Availability Check with Click & Collect logic
    * [#252291](https://tp.digidirect.com/252291) -- [AUPost Setting] As an admin, I want to be able to upload AUPost post code file for getting of geo-coordinates

* Bugfixes:
    * [#255762](https://tp.digidirect.com/255762) -- [Aupost Setting] C&C error on search by Aupost post code file


2.6.0
=============
* New features:
    * [#258093](https://tp.digidirect.com/258093) -- [AUPost Coordinates] As an admin, I want AUPost post code to calculate average coordinates on file upload

2.6.1
=============
* Bugfixes:
    * [#259173](https://digidirect.tpondemand.com/entity/259173) -- [Availability Check][Click&Collect] Null is received for the coordinates if user check stores for C&C on PDP

2.6.2
=============
* Bugfixes:
    * [#260123](https://digidirect.tpondemand.com/entity/260123) -- [PROJECT: PLATYPUS][AUSPOST] Place cannot be correctly detected when not all entries with the same postcode have latitude and longitude entries

2.6.3
=============
* Bugfixes:
    * [#258539](https://digidirect.tpondemand.com/entity/258539) -- [COLLECT V2] Order is not split correctly for 'СС+Delivery' items in cart due to shipping method error

2.6.4
=============
* Bugfixes:
    * [#262098](https://digidirect.tpondemand.com/entity/262098) -- [AVAILABILITY CHECK C&C] It's unavailable to find/select store for C&C on the cart page


2.7.0
=============
* New features:
    * [#240878](https://digidirect.tpondemand.com/entity/240878) -- [Multiple Delivery Method] As an admin, I want Order detail page to be changed if multiple delivery method enabled
    * [#241266](https://digidirect.tpondemand.com/entity/241266) -- [Multiple Delivery Method][Click & Collect] As a system, I want to be able to use Click & Collect as a delivery method

* Bugfixes:
    * [#263097](https://digidirect.tpondemand.com/entity/263097) -- [PROJECT: PLATYPUS][SDD] 2 of the same products are displayed in the cart and not 1 product with Qty 2

2.7.1
=============
* Bugfixes:
    * [#264657](https://digidirect.tpondemand.com/entity/264657) -- [PROJECT: TAF][FULFILMENT OPTIONS] Error message is shown though both products are available at 1 store

* Improvements
    * Add ajax action to check if we already have a delivery item in the cart


2.8.0
=============
* New features:
    * [#268396](https://digidirect.tpondemand.com/entity/268396) -- [FIRST STORE CHECK] As a system, I want to be able to check if a product added to the cart is available for a previously added product's store
    * [#271798](https://digidirect.tpondemand.com/entity/271798) -- [PRODUCT EDIT PAGE] A a customer, I want to be able to edit product Delivery Method if there is only one product in the cart

* Bugfixes:
    * [#260125](https://digidirect.tpondemand.com/entity/260125) -- [MDM] [C&C] There are labels and message from basic C&C extension
    * [#266000](https://digidirect.tpondemand.com/entity/266000) -- [PROJECT TAF][PDP][C&C] СС tab is not preselect when cart already contains CC product
    * [#266758](https://digidirect.tpondemand.com/entity/266758) -- [PROJECT: TAF] Delivery product is not added to shopping cart when it already contains C&C item

2.8.1
=============
* Bugfixes:
    * [#274924](https://digidirect.tpondemand.com/entity/274924) -- [PROJECT: TAF][PDP][C&C] Incorrect text label on 'Add to cart' button
    * [#279227](https://digidirect.tpondemand.com/entity/279227) -- [Project: Platypus][PDP + Optimization][Dr.Martens] Where Can i try this link is disabled or shown twice


2.9.0
=============
* New features:
    * [#278826](https://digidirect.tpondemand.com/entity/278826) -- [Single C&C for Cart] As a user, I want to be able one C&C store for all items in the cart
    * [#278827](https://digidirect.tpondemand.com/entity/278827) -- [Single C&C for Cart] As an admin, I want 'Single C&C for Cart' variation to be added

* Bugfixes:
    * [#273922](https://digidirect.tpondemand.com/entity/273922) -- [Product edit page] Product with different stores are available for the order


2.10.0
=============
* New features:
    * [#279278](https://digidirect.tpondemand.com/entity/279278) -- [C&C][Abstract Entity] As a user, I want to be able to select click and collect store
    * [#284054](https://digidirect.tpondemand.com/entity/284054) -- [CART PAGE] As a customer, I want to be able to edit product Delivery Method if there is only one product in the cart

* Bugfixes:
    * [#274931](https://digidirect.tpondemand.com/entity/274931) -- [Extended Cart] After adding Up-sell product on PDP popup is not appeared


2.11.0
=============
* New features:
    * [#171449](https://digidirect.tpondemand.com/entity/171449) -- [SHIPPING METHOD VARIATIONS SETTINGS] As an admin I want a variation of a Click & Collect shipping method to be created
    * [#290155](https://digidirect.tpondemand.com/entity/290155) -- As an admin, I want set up Length Unit to be used in Click & Collect module
    * [#290156](https://digidirect.tpondemand.com/entity/290156) -- [ENABLE C&C CART/PDP] As an admin, I want to be able to enable/disable C&C on Cart and PDP pages

* Bugfixes:
    * [#291072](https://digidirect.tpondemand.com/entity/291072) -- 'Click&Collect Variation' is displayed for store view and website but works on global scope

2.11.1
=============
* Bugfixes:
    * [#277548](https://digidirect.tpondemand.com/entity/277548) -- [CHECKOUT ZONE] Label on the billing address tab disappears after reloading the page on payment page
 

2.12.0
=============
* New features:
    * [#287679](https://digidirect.tpondemand.com/entity/287679) -- [CHECKOUT] As a customer, I want to be able to view collect places on checkout
    * [#295812](https://digidirect.tpondemand.com/entity/295812) -- [MSI & COLLECT] As a user, I want to be able to see availability status in cart/checkout click&collect stores
    * [#297705](https://digidirect.tpondemand.com/entity/297705) -- [Locator][Click & Collect] As a system, I want availability flag to be added
    * [#278827](https://digidirect.tpondemand.com/entity/278827) -- [Single C&C for Cart] As an admin, I want 'Single C&C for Cart' variation to be added
 

2.13.0
=============
* New features:
    * [#295601](https://digidirect.tpondemand.com/entity/295601) -- [C&C][Abstract Entity] As a customer, I want Shipping Address fields to be hidden and pre-filled with an Abstract Entity values on Checkout

2.13.1
=============
* Bugfixes:
    * [#300965](https://digidirect.tpondemand.com/entity/300965) -- [C&C][Abstract Entity] User can proceed to the checkout step 2 without selecting store for click and collect

2.13.2
=============
* Bugfixes:
    * [#300965](https://digidirect.tpondemand.com/entity/300965) -- [C&C][Abstract Entity] User can proceed to the checkout step 2 without selecting store for click and collect

2.13.3
=============
* Bugfixes:
    * [#303737](https://digidirect.tpondemand.com/entity/303737) -- "My billing and.." checkbox for the payment method is unchecked after trying to place an order with invalid data in custom checkout field

2.13.4
=============
* Bugfixes:
    * [#307859](https://digidirect.tpondemand.com/entity/307859) -- [Project: DigiDirect] Shipping address is not displayed on the second checkout step for the logged in users

2.13.5
=============
* Bugfixes:
    * [#321136](https://digidirect.tpondemand.com/entity/321136) -- [Charity] Charity amount is not re-counted if user goes back to 1st step of Checkout and changes payment method


2.14.0
=============
* New features:
    * [#304091](https://digidirect.tpondemand.com/entity/304091) -- [ADD C&C ATTR] As an admin, I want C&C orders placed by users to include Collect Place attribute

2.14.1
=============
* Bugfixes:
    * [#311110](https://digidirect.tpondemand.com/entity/311110) -- [Project: RipCurl][C&C] The Customer is not available to place an order with delivery method when C&C order was interrupted

2.14.2
=============
* Bugfixes:
    * [#322581](https://digidirect.tpondemand.com/entity/322581) -- [PROJECT: Sportsgirl] Shipping methods are not updated after change Сountry or Zipcode

2.14.3
=============
* Bugfixes:
    * [#323381](https://digidirect.tpondemand.com/entity/323381) -- [PROJECT: Sportsgirl] Payload extender should be added to the default shipping-save-processor

2.14.4
=============
* Bugfixes:
    * [#330325](https://digidirect.tpondemand.com/entity/330325) -- [PROJECT: Sportsgirl] The "Place order" button is not active after clicking the "Charity" checkbox


2.15.0
=============
* New features:
    * [#331347](https://digidirect.tpondemand.com/entity/331347) -- [PRIVATE CONTENT] As a system, I want '_isPrivateScope' parameter to be removed from Digidirect_collect module

2.15.1
=============
* Bugfixes:
    * [#334850](https://digidirect.tpondemand.com/entity/334850) -- [TAF][PHP Upgrade][digidirect_collect] Exception error on C&C request

2.15.2
=============
* Bugfixes:
    * [#339239](https://digidirect.tpondemand.com/entity/339239) -- [TAF][PHP Upgrade][digidirect_collect] Exception error on C&C request

2.15.3
=============
* Bugfixes:
    * [#346201](https://digidirect.tpondemand.com/entity/346201) -- [PROJECT: SussanGroup][CHECKOUT STEP 1][C&C] A grey inactive screen occurs after a double change of the DTS place

2.15.4
=============
* Bugfixes:
    * [#360086](https://digidirect.tpondemand.com/entity/360086) -- [TAF][Magento 2.3.3 Upgrade][Fluent Fulfilment options] Error message text is incorrect


2.16.0
=============
* New features:
    * [#357437](https://digidirect.tpondemand.com/entity/357437) -- [Abstract Entity][My Store][Click&Collect] As a admin, I want following settings to be added

