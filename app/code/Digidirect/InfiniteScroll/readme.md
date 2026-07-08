Digidirect Infinite Scroll
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Infinite+Scroll)

### Version 1.0.0

Infinite Scroll module allows you to lazy load listing page content
Core version supports following pages:
1. Catalog listing page
2. Catalog search page

How To add infinite scroll for custom page:
===========================================

If you want to add infinite scroll for any custom page, you have to do following steps:

1. Create infinitescroll.xml file and place it to the Module/etc directory

```html
<?xml version="1.0" ?>

<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:digidirect:module:Digidirect_InfiniteScroll:etc/infinitescroll.xsd">
    <scroll name="uniqname">
        <config handle="custom_page_handle" instance="Vendor\Module\Model\Processor" selector=".custom-selector"/>
    </scroll>
</config>
```

`handle` - handle of the custom page where infinite scroll should be initialized
`instance` - class which will be responsible for the content generation
`selector` - element on the page where response content will be appended

2. Class `Vendor\Module\Model\Processor` should implement `Digidirect\InfiniteScroll\Model\ProcessorInterface`

3. If you need to add custom layout update for your infinite scroll page, you need to create file `infinitescroll_{custom_page_handle}.xml` with custom layout updates for `{custom_page_handle}` page and put here your updates

All admin settings for custom infinite scroll should be placed under `digidirect_infinitescroll_config` section

### Version 1.0.1

1. Bugfix #161141 Swatch doesn't work on product listing
2. Bugfix #161676 Loaded products are duplicated in 'Recently bought' block

### Version 1.0.2

1. Bugfix #166271 Eternal spinner appears on the Product Listing page if there is a product with MSRP price
2. Bugfix #173428 Console error on add to wish list clicking

### Version 1.0.3

1. Bugfix #179777 PROJECT: Pahrmacy Online. Category Listing: Load more button doesn't display

### Version 1.0.4

1. Bugfix #175984 Infinite scroll. Page is broken on 'Add to compare'

### Version 1.1.0

1. [BACK POSITION] As a customer, I want to be able to return to the exact Infinite Scroll position using browser back button
