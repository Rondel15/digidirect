Custom Checkout Fields
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Custom+Checkout+fields)

**Description:**

The module allows you to add custom fields on checkout and save 
its in a separate table

Supported types of custom fields:
- checkbox
- date
- text
- multiselect
- radio
- select
- textarea

How to Add a Custom Field on Checkout:
===========================================

Use a checkout_fields.xml file in the "public_html/digidirect_xml/" folder to config your
own custom fields on a checkout. In this file you can find examples.

Code must be unique

```html
<?xml version="1.0"?>
<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
        xsi:noNamespaceSchemaLocation="urn:digidirect:module:Digidirect_CheckoutFields:etc/checkout_fields.xsd">
    <field id="textarea_field">
        <frontend_name>Textarea field</frontend_name>
        <frontend_input>textarea</frontend_input>
        <!-- For date fields can be used format -->
        <frontend_format>d/m/Y</frontend_format>
        <sort_order>10000</sort_order>
        <validation>
            <rule name="required-entry">1</rule>
            <rule name="less-than-equals-to">20</rule>
            <rule name="validate-number">1</rule>
            <rule name="validate-digits">1</rule>
            <rule name="validate-zero-or-greater">1</rule>
        </validation>
        <area>
            <checkout_step>billing-step</checkout_step>
            <custom_scope>payment</custom_scope>
            <fieldset>beforeMethods</fieldset>
        </area>
    </field>

    <!-- OPTIONAL --> 
    <options>
        <option value="0" label="First option" />
        <option value="56" label="Twenty five option" />
        <option value="23" label="Second option" />
    </options>
</config>
```

### VERSION 1.0.0
  1. Developer is able to create xml file with required fields
  2. Admin is able to enable/disable fields on frontend
  3. Customer is able to fill required/optional fields
  4. Admin is able to observe custom fields values on order level
  
### VERSION 1.1.0
  
  1. Custom checkout fields are enabled in admin area
  2. Custom checkout fields are enabled on PayPal order review page
  3. Added ability to add frontend class for custom checkout fields
  
### VERSION 1.2.0

  Developer is able to display field or not depends on special conditions. 
  
  Example of condition:
  
```xml
<virtualType name="CheckoutFieldsConditionProductAttribute" type="Digidirect\CheckoutFields\Model\Condition\ProductAttribute">
    <arguments>
        <argument name="validateFields" xsi:type="array">
            <item name="script_save" xsi:type="array">
                <item name="prescription" xsi:type="string">1</item>
            </item>
        </argument>
    </arguments>
</virtualType>

<type name="Digidirect\CheckoutFields\Helper\Xml\Fields\Parser">
    <arguments>
        <argument name="conditions" xsi:type="array">
            <item name="product_attribute" xsi:type="object">CheckoutFieldsConditionProductAttribute</item>
        </argument>
    </arguments>
</type>
```  

### VERSION 1.2.1
    
  Add compatibility for Digidirect Click & Collect module: bug 185876 - Checkout Fields + C&C - shipping address block is not hidden on checkout page
  
###  VERSION 1.3.1

  Fixed issue if custom fields are added only on billing step. Previously these fields were not saved.
  Fixed shipping step issue. Logged In customer could not use his saved address.
  
###  VERSION 1.4.0
    
   1. As a system, I want to be upgraded to ver. 2.2
  
###  VERSION 1.5.0

   1. As an Administrator, I want to be able to show Custom Checkout field on product detail page
  
###  VERSION 1.5.1

   1. Bugfixes
  
###  VERSION 1.5.2

   1. Code improvements
  
###  VERSION 1.5.3

   1. Bugfixes
  
###  VERSION 1.5.4

   1. Bugfixes
  
###  VERSION 1.5.5

   1. Bugfixes
  
###  VERSION 1.5.5

   1. Bugfixes

###  VERSION 1.6.0

   1. As an Administrator, I want to be able to edit Custom Checkout field in back-office
   2. Bugfixes
  
###  VERSION 1.6.1

   1. Bugfixes
  
###  VERSION 1.6.2

   1. Bugfixes

###  VERSION 1.7.0

   1. As an admin, I wand dependencies between checkout field could be made via XML file
   2. [Custom Checkout Fields][Product Subscriptions] As an admin, I want to be able to specify fields that should be editable in subscriptions
   3. [Custom Checkout Fields][Product Subscriptions] As a user, I want new checkout field value to update existing subscription after order placing