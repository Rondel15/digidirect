1.0.0
=============
* Solution Architecture:
    * [#129518](https://digidirect.tpondemand.com/entity/129518) -- Utilities. Solution Architecture

* New features:
    * Enable / disable wrap in parent tag in wysiwyg editor
    * Configure allowed childs in wysiwyg editor
    * Configure allowed childs tags in wysiwyg editor
    * Configure prefixes for default magento entities like Order, Invoice, Shipment, CreditMemo, Rma on website level
    * Specify custom text or use store_id as prefix
    * Prefixes to be added to corelated entities that will be created in future
    * Manage the quality of the images for the pages to increase page load speed

1.1.0
=============
* New features:
    * Specify the quality of the product images by type in view.xml

1.1.1
=============
* Bugfixes:
    * Remove Sales Prefix when the option is disabled

1.1.2
=============
* Bugfixes:
    * Ability to configure sales prefixes on the Website level only

1.1.3
=============
* Bugfixes:
    * Change store configuration labels

1.1.4
=============
* Improvements:
    * Async SVG icons
    * JS component requirements (ES6)

1.1.4.1
=============
* Improvements:
    * Added custom reader - provide ability to read xml configuration files from directory digidirect_xml in root magento directory

1.1.4.2
=============
* Bugfixes:
    * Fixed wordings, fixed acl

1.1.5
=============
* Improvements:
    * Ability to add a custom collection of urls to the sitemap

1.1.6
=============
* Improvements:
    * PubSubExtend alias.
    * JS utilities: LoadView.
    * ESlint of the components.

1.1.7
=============
* Improvements:
    * The 'getTestSelector' method has been added in helper. It adds 'data-test' attribute only in developer mode in phtml. Attribute necessary for FE testing. How to use:
        `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getTestSelector('formLogin'); ?>

1.2.0
=============
* Bugfixes:
    * Fixed ACL Resource name in system.xml
    * As an admin, I want to be able to see the version of the extension installed in backend

1.2.1
=============
* Improvements:
    * PubSubExtend added

1.2.2
=============
* Bugfixes:
    * Fixed default magento bug. When create widget and try to add it to "404 No Route" - it doesn't work. Changed handle from cms_index_noroute to cms_noroute_index. US#175986

* Improvements:
    * JS component: Multi Store View compatibility
    * Check cart items for specified product attribute value
      `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasCartProductAttributeValue('prescription', 1)) : ?>`
    * Check order items for specified product attribute value
      Examples:
      Using order increment ID:
      `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasOrderProductAttributeValue($block->getOrderId(), 'prescription', 1)) : ?>`
      Or using order ID:
      `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasOrderProductAttributeValue($order->getId(), 'prescription', 1, false)) : ?>`

1.3.0
=============
* New features:
    * [#175986](https://digidirect.tpondemand.com/entity/175986) -- CONTAINER NO-ROUTE CMS. As an admin, I want to be able to add widget to "CMS no-Route Page" page

1.3.1
=============
* Improvements:
    * Check product items for specified product attribute value:
      `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->checkItemsForAttributeValue($products, 'msrp')) : ?>`

1.3.2
=============
* Improvements:
    * Get current deployment mode:
      `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getDeploymentMode() ?>`

* Bugfixes:
    * [#189369](https://digidirect.tpondemand.com/entity/189369) -- The version of extension is not displayed in the back-office->Stores->Configuration->Advanced->Advanced

1.4.0
=============
* New features:
    * [#190426](https://digidirect.tpondemand.com/entity/190426) -- As a system, I want "Enabled Modules" functionality to be available on the frontend side (see description)

1.5.0
=============
* New features:
    * [#189322](https://digidirect.tpondemand.com/entity/189322) -- As a system, I'd like to be ready to use off-canvas component for LN (see description)
    * [#191490](https://digidirect.tpondemand.com/entity/191490) -- MESSAGES. As a frontend developer, I want to be able to customize messages on front end

* Bugfixes:
    * [#193773](https://digidirect.tpondemand.com/entity/193773) -- Utilities. Scope for 'Message Settings' is incorrect
    
1.6.0
=============
* New features:
    * [#196276](https://digidirect.tpondemand.com/entity/196276) -- Magento 2.2+ Utilities. As a system, I want to has a compatibility between Magento v2.2.n and extension    

1.7.0
=============
* New features:
    * [#202525](https://digidirect.tpondemand.com/entity/202525) -- Move functionality of Digidirect_Email module to Utilities
    
* Bugfixes:
    * [#195440](https://digidirect.tpondemand.com/entity/195440) -- ALL. Wishlist. Adding to wishlist message is empty

1.8.0
=============
* New features:
    * [#205941](https://digidirect.tpondemand.com/entity/205941) -- CUSTOM MEDIA TYPE. As an admin, I want to be able to use any custom media type in view.xml
* Bugfixes:
    * [#190248](https://digidirect.tpondemand.com/entity/190248) -- An error occurs on the website if enable the custom loading animation with quote in its text

1.9.0
=============
* New features:
    * [#208339](https://digidirect.tpondemand.com/entity/208339) -- PRODUCT ATTRIBUTES TO JSON. As a system I should be able to add attribute of Simple Product to Configurable's JSON-config

1.10.0
=============
* New features:
    * [#202639](https://digidirect.tpondemand.com/entity/202639) -- [M2.2] As an admin, I want to be able to see all installed modules with version information in Magento 2.2 admin

1.11.0
=============
* Improvements:
    * Ability to customize any messages via BackOffice:
      `<?php echo $this->helper('Digidirect\Utilities\Helper\Message')->getCustomizedMessage($message, $template = null); ?>`

1.12.0
=============
* New features:
    * [#218037](https://digidirect.tpondemand.com/entity/218037) -- [CUSTOM ATTRIBUTES] As a user, I want to see custom product attributes on Cart and Checkout
    * [#216017](https://digidirect.tpondemand.com/entity/216017) -- [Message Customization] As a frontend developer, I want to be able to customize stock messages on frontend
    
1.13.0
=============
* New features:
    * [#216112](https://digidirect.tpondemand.com/entity/216112) -- As a system, I want AJAX functionality to be available for the Add To Compare button in Base theme
    * [#219689](https://digidirect.tpondemand.com/entity/219689) -- As an extension, I should be able to work on Magento 2.1.8    
    
1.13.1
==============
* Bugfixes:
    * [#214993](https://digidirect.tpondemand.com/entity/214993) -- The Store image attaches as a placeholder in back-office    
    
1.14.0
==============
* Improvements:
    * Ability to pass email template that needs new templates variables (see readme file for example)
    * Ability to get any custom variables or config using Digidirect Utilities helper

1.15.0
==============
* Bugfixes:
    * [#215960](https://digidirect.tpondemand.com/entity/215960) -- [PROJECT: TAF][My account][Wishlist] A message doesn't appear when new product is added to wishlist

1.15.1
==============
* Improvement:
    * ``?isAjax=1`` -- Ability to use this param for any POST requests to Magento as Ajax query.
    
1.16.1
===============
* Improvement:
    * Added ability to get last order on checkout success page.   

1.16.2
===============
* Improvement:
    * Abstract Searchable Drop-Down
    
1.16.3
================
* Improvements:
    1. Added Cms Helper to filter wysiwyg content (for example variables)
    2. Added Store Helper to have ability get current store information
    
1.16.4
================
* Bugfixes:
    * [#256106](https://digidirect.tpondemand.com/entity/256106) -- [Multiple Delivery] Can't disable module
    
1.16.5
================
* Improvements:
    1. Added payment method rendering helper with correct theme    
    
1.16.6
================
* New features:
    * [#265259](https://digidirect.tpondemand.com/entity/265259) -- As a developer, I want to be able to add Reset button to any searchable dropdown in Magento admin panel

1.16.7
================
* Bugfixes:
    * [#266709](https://digidirect.tpondemand.com/entity/266709) -- [Project: Neverfail] BE:Outlets: Searchable dropdowns: Expanded 'Bill Group' dropdown is not fully visible. The list goes under the screen
    * [#267291](https://digidirect.tpondemand.com/entity/267291) -- [Utilities One searchable drop down is not closed on clicking on another one

1.16.8
================
* Bugfixes:
    * [#268608](https://digidirect.tpondemand.com/entity/268608) -- [PROJECT: NEVERFAIL] The response for the Checkout request takes 12-15 sec instead of up to 5 sec

1.16.9
================
* Bugfixes:
    * [#270147](https://digidirect.tpondemand.com/entity/270147) -- [Project: Ultraceuticals] Exception on attempt to see order details from back-office under certain conditions

1.17.0
================
* New features:
    * [#272022](https://digidirect.tpondemand.com/entity/272022) -- As a developer, I want to see theme switcher via store view
    
1.18.0
================
* New features:
    * [#277612](https://digidirect.tpondemand.com/entity/277612) -- [INSERT FILE] As admin, I want to be able to upload and insert a hyperlink to a file using WYSIWYG Editor

1.18.1
================
* Bugfixes:
    * [#282214](https://digidirect.tpondemand.com/entity/282214) -- [Project: RipCurl] 'Unable to unserialize value.' error appears when user add image to CMS block/banner/page

1.19.0
================
* New features:
    * [#277612](https://digidirect.tpondemand.com/entity/277612) -- [INSERT FILE] As admin, I want to be able to upload and insert a hyperlink to a file using WYSIWYG Editor
    * [#283118](https://digidirect.tpondemand.com/entity/283118) -- [Magento 2.3] As a system, I want the following LEGO extensions to be compatible with Magento 2.3
* Bugfixes:
    * [#274931](https://digidirect.tpondemand.com/entity/274931) -- [Extended Cart] After adding Up-sell product on PDP popup is not appeared
    * [#275900](https://digidirect.tpondemand.com/entity/275900) -- [Project: Neverfail] [Customer Management]: BE: The settings are not fully loaded under Configuration -> Digidirect -> B2B
    * [#277791](https://digidirect.tpondemand.com/entity/277791) -- [Utilities][Searchable dropdown] Some results are not visible
    * [#283477](https://digidirect.tpondemand.com/entity/283477) -- [Utilities] It's impossible to open re-saved Outlet in admin panel
    * [#284502](https://digidirect.tpondemand.com/entity/284502) -- [M2.3][Gift Card Images] Default thumbnail is shown instead uploaded image in admin panel and on PDP

1.19.1
================
* Bugfixes:
    * [#285548](https://digidirect.tpondemand.com/entity/285548) -- [M2.3] WYSIWYG settings are not working on Magento 2.3

1.19.2
================
* Bugfixes:
    * [#285548](https://digidirect.tpondemand.com/entity/285548) -- [M2.3] .SVG-file is not uploaded if it set up in allowed file types for WYSIWIG
    * [#285548](https://digidirect.tpondemand.com/entity/285548) -- [M2.3] Default placeholder is shown in WYSIWYG redactor instead image

1.19.3
================
* Bugfixes:
    * [#290401](https://digidirect.tpondemand.com/entity/290401) -- [M2.3][Newsletter] 500 error if user try to place Order with enabled newsletter module

1.19.4
================
* Bugfixes:
    * [#294917](https://digidirect.tpondemand.com/entity/294917) -- [Project:CRV] Validation error appears while user uploads pdf file in spite this file type is allowed in settings
    
1.20.0
================
* New features:
    * [#296975](https://digidirect.tpondemand.com/entity/296975) -- [COMPARE EVENT] As an admin, I want Compare Event logic to be changed

1.20.1
================
* Bugfixes:
    * [#295240](https://digidirect.tpondemand.com/entity/295240) -- [Project DigiDirect] [Abstract Integration] [Magento 2.3] Email can not be sent with attached Log-file
    * [#298913](https://digidirect.tpondemand.com/entity/298913) -- [C&C][Abstract Entity] Postcode field is removed from the 'Click and Collect Fields to Set Up Store' table instead of 'Pre-fill Shipping Address Fields'
