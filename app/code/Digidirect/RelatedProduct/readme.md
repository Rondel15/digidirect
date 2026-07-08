Digidirect Related Product
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Related+Product+Info)

Version 1.0.0

How To add custom product attributes to collection via layout:
===========================================

```html
<block class="Digidirect\RelatedProduct\Block\ProductList\Related" name="catalog.product.related" template="Magento_Catalog::product/list/items.phtml">
    <arguments>
        <argument name="product_attributes" xsi:type="array">
            <item name="0" xsi:type="string">required_options</item>
            <item name="1" xsi:type="string">custom_attribute_1</item>
            <item name="2" xsi:type="string">custom_attribute_2</item>
        </argument>
    </arguments>
</block>
```

Version 2.0.0

Refactoring & Testing

Version 2.3.0

Compatible with Magento Commerce (EE) 2.3
