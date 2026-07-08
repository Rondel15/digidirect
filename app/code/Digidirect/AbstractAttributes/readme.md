Digidirect Abstract Attributes
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Abstract+Attributes)

The extension will allow to admin to manage extended option fields. For example, brands.
The following functionality will be implemented.

##### An admin is able to:
- able to assign every product to an abstract attribute;
- specify additional information about each abstract attribute including abstract attribute description, abstract attribute thumbnail;
- mark an attribute as featured so that it appears on featured attribute widget;
- embed featured attribute widget into a CMS block;
- an admin is able to specify URL address, meta title and meta description for the attribute and attribute option listing page;
- attribute listing page and attribute option listing page will be added to the sitemap
- enable extended attribute management for any attribute with dropdown or multiple selector type
- enable "All Attribute Options" landing page with full list of abstract attributes options represented by abstract attribute thumbnails for the website

##### User can see:
- see abstract attribute that were marked as "Include in Menu" in website navigation
navigate to abstract attribute listing pages by clicking on a abstract attribute in navigation or featured abstract attribute widget
- see a list of featured brands represented by brand thumbnails on a featured brands widget
- see brand thumbnail and navigate to abstract attribute or listing pages by clicking on it
navigate to "All Attribute" landing page with full list of abstract attributes options represented by abstract attribute thumbnails and featured attribute options widget
- see all products assigned to a specific abstract attribute value when navigating to abstract attribute listing page

##### InfiniteScroll:
- Please install "digidirect/abstractattributesinfinitescroll" addon.

##### LayeredNavigation:
- Please install "digidirect/abstractattributeslayerednavigation" addon.

### VERSION 1.4.0

1. Added created and modified columns in digidirect_aa_options table

### VERSION 2.0.0

1. Added compatibility with magento 2.1.8 CE and EE. 
2. Due incompatible changes done by Magento in \Magento\Catalog\Model\Product\Image - constructor argument 
\Magento\Catalog\Model\View\Asset\ImageFactory $assetImageFactory was removed, extension VERSION 2.0.0 incompatible with
Magento version < 2.1.8 

### VERSION 3.0.0

1. Magento 2.2 Compatibility
