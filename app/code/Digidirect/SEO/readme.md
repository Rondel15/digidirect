SEO
================

[wiki link](https://wiki.digidirect.com/display/LEGO/SEO)

### Description
The extension is used for search engine optimization

### VERSION 1.0.0.0
  1. Allows to populate category meta description with category description if meta description is empty and meta title with category name
  2. Allows to enabled/disable this functionality in "Stores/Configuration/Catalog/Search Engine Optimization => Enable Auto-Generate Category Meta Information"

### VERSION 1.0.0.1
  1. strips tags(for categories) and limits meta description length to 160 symbols
  
### VERSION 1.1.0
  1. Add rel=prev and rel=next tags to appear on category listing (only if filters were not applied) to ensure that paginated content is indexed correctly by Google.
  This functionality work only if filters were not applied because to be sure that Magento product collection was not loaded before product listing block didn't load it.
  
### VERSION 1.1.1
  1. Fix dependency on "Utilities" extension
  
### VERSION 1.1.2
  1. Added ability to remove trailing slash from url
  
### VERSION 1.1.3
  1. Fixed getDeleteUrl() on customer address book page. If "trailing slash" is disabled - do not trim slash for that url

### VERSION 1.1.4
  1. Search Results - Product added from search results not shown in the cart sidebar until any product added from the category list

### VERSION 1.2.0
  1. Exclude Category from sitemap
  
### VERSION 1.3.0
  1. Added facebook open graph meta tags
  
### VERSION 1.4.0
  1. Added openGraph for cms pages and catalog category listing page
  2. Added Twitter big image for product and categories 
  
