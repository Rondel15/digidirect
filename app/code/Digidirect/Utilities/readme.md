Digidirect Utilities
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Utilities)

### Description
The extension expands standard Magento functionality

### VERSION 1.0.0

  1. Enable / disable wrap in parent tag in wysiwyg editor
  2. Configure allowed childs in wysiwyg editor
  3. Configure allowed childs tags in wysiwyg editor
  4. Configure prefixes for default magento entities like Order, Invoice, Shipment, CreditMemo, Rma on website level
  5. Specify custom text or use store_id as prefix
  6. Prefixes to be added to corelated entities that will be created in future
  7. Manage the quality of the images for the pages to increase page load speed

### VERSION 1.1.0

  1. Specify the quality of the product images by type in view.xml

### VERSION 1.1.1

  1. Bugfix - Remove Sales Prefix when the option is disabled

### VERSION 1.1.2

  1. Ability to configure sales prefixes on the Website level only

### VERSION 1.1.3

  1. Change store configuration labels

### VERSION 1.1.4

  1. Async SVG icons
  2. JS component requirements (ES6)

### VERSION 1.1.4.1

  1. Added custom reader - provide ability to read xml configuration files from directory digidirect_xml in root magento directory

### VERSION 1.1.4.2

  1. Fixed wordings, fixed acl

### VERSION 1.1.5.0

  1. Ability to add a custom collection of urls to the sitemap

### VERSION 1.1.6.0

  1. PubSubExtend alias.
  2. JS utilities: LoadView.
  3. ESlint of the components.

### VERSION 1.1.7.0

  1. The 'getTestSelector' method has been added in helper. It adds 'data-test' attribute only in developer mode in phtml. Attribute necessary for FE testing. How to use:
  `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getTestSelector('formLogin'); ?>`

### VERSION 1.2.0.0

  1. Fixed ACL Resource name in system.xml
  2. Bugfix 161105. As an admin, I want to be able to see the version of the extension installed in backend

### VERSION 1.2.1.0

  1. PubSubExtend added

### VERSION 1.2.2

  1. Fixed default magento bug. When create widget and try to add it to "404 No Route" - it doesn't work. Changed handle from cms_index_noroute to cms_noroute_index. US#175986   
  2. JS component: Multi Store View compatibility
  3. Check cart items for specified product attribute value
  `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasCartProductAttributeValue('prescription', 1)) : ?>`
  4. Check order items for specified product attribute value
    Examples:  
    Using order increment ID:    
    `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasOrderProductAttributeValue($block->getOrderId(), 'prescription', 1)) : ?>`
    Or using order ID: 
    `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->hasOrderProductAttributeValue($order->getId(), 'prescription', 1, false)) : ?>`

### VERSION 1.3.0

  1. As an admin, I want to be able to add widget to "CMS no-Route Page" page

### VERSION 1.3.1

  1. Check product items for specified product attribute value:
     `<?php if ($this->helper('Digidirect\Utilities\Helper\Attribute')->checkItemsForAttributeValue($products, 'msrp')) : ?>`

### VERSION 1.3.2

  1. Get current deployment mode:
     `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getDeploymentMode() ?>`

### VERSION 1.4.0

  1. As a system, I want "Enabled Modules" functionality to be available on the frontend side (see description)

### VERSION 1.4.1

  1. As a frontend developer, I want to be able to customize messages on frontend: 
  Added configuration to Stores->Configuration->Advanced->Developer->Message Configuration
  Need to specify following fields to customize message:
   - type - specify 'Block' type, if you need to add additional style for message block or use custom template for renderer;
   - identifier - default message text, which should be modified;
   - phrase - new message text, this field should be blank, if you don't need to change default message text;
   - template - should be specified for 'Block' type, if this field is blank, the default template will be used for custom message renderer;
   - class - css class name.

### VERSION 1.5.0

  1. See CHANGELOG.md

### VERSION 1.6.0

  1. Added compatibility with magento 2.2:
     - Removed \Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page::render() rewrite
     - Added \Digidirect\Utilities\Preference\Magento\Framework\View\Result\Page::renderPage() rewrite. Additional variables are now being assigned in this method

### VERSION 1.7.0

  1. Move functionality of Digidirect_Email module to Utilities. Now developer can add new/custom email template variable using next steps:
     - add to di.xml in your module code like this:
     
     `<type name="Digidirect\Utilities\Preference\Magento\Framework\Mail\Template\TransportBuilder">
         <arguments>
             <argument name="customTemplateVars" xsi:type="array">
                 <item name="subscriber" xsi:type="object">Digidirect\Newsletter\Model\MailTemplateCustomVar</item>
             </argument>
         </arguments>
      </type>
     `
     
     - create a class (for example Digidirect\Newsletter\Model\MailTemplateCustomVar) and realize logic of new/custom email template variable getting in getVars(\Magento\Framework\Mail\Template\TransportBuilder $subject, array $templateVars = null) method (example you can see in Digidirect_Newsletter module)
     
  
  2. Bugfixes (See CHANGELOG.md)

### VERSION 1.8.0

  1. Ability to override XSD schemes:
     - add to di.xml in your module code like this:

    `<type name="Digidirect\Utilities\Plugin\Magento\Framework\Config\Dom\UrnResolver">
          <arguments>
              <argument name="overwrittenSchemas" xsi:type="array">
                  <item name="urn:magento:framework:Config/etc/view.xsd" xsi:type="string">urn:digidirect:module:Digidirect_Utilities:etc/view.xsd</item>
              </argument>
          </arguments>
      </type>

### VERSION 1.9.0

  1. Ability to add additional simple product attributes to jsonConfig:
     - add to catalog_product_view_type_configurable.xml in your theme config like this:

    `<referenceBlock name="product.info.options.swatches">
         <arguments>
             <argument name="additional_attributes" xsi:type="array">
                 <item name="sku" xsi:type="string">Product Sku</item>
             </argument>
         </arguments>
     </referenceBlock>`

### VERSION 1.10.0

  1. [M2.2] As an admin, I want to be able to see all installed modules with version information in Magento 2.2 admin

### VERSION 1.11.0

  1. Ability to customize any messages via BackOffice:
     `<?php echo $this->helper('Digidirect\Utilities\Helper\Message')->getCustomizedMessage($message, $template = null); ?>`

### VERSION 1.12.0
 
  1. [CUSTOM ATTRIBUTES] As a user, I want to see custom product attributes on Cart and Checkout
  2. [Message Customization] As a frontend developer, I want to be able to customize stock messages on frontend
  
### VERSION 1.12.0
   
   1. System Configuration Form Creation
        - Digidirect\Utilities\Model\Source\AttributeList - get all attribute in format ['attr_code' => 'attr_label'] 
        you can config it like 
        `<virtualType name="Digidirect\B2bMyStore\Model\Source\AbstractEntity\AttributeList" type="Digidirect\Utilities\Model\Source\AttributeList">
            <arguments>
                <argument name="entity" xsi:type="const">\Digidirect\AbstractEntity\Model\AbstractEntity::ENTITY_TYPE</argument>
            </arguments>
        </virtualType>`
        - Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Attribute\Renderer\AbstractRenderer -
        default select renderer who take options from Digidirect\Utilities\Model\Source\AttributeList. Example:
        `<virtualType name="Digidirect\B2bMyStore\Block\Adminhtml\Config\Form\Field\Attribute\Renderer\AbstractEntity"
              ype="Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Attribute\Renderer\AbstractRenderer">
            <arguments>
                <argument name="attributeList" xsi:type="object">Digidirect\B2bMyStore\Model\Source\AbstractEntity\AttributeList</argument>
            </arguments>
        </virtualType>`
        - Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Relation dynamic form. Example:
        `<virtualType name="Digidirect\B2bMyStore\Block\Relation" type="Digidirect\Utilities\Block\Adminhtml\Config\Form\Field\Relation">
           <arguments>
                 <argument name="config" xsi:type="array">
                      item name="address" xsi:type="array">
                         <item name="class" xsi:type="object">Digidirect\B2bMyStore\Block\Adminhtml\Config\Form\Field\Attribute\Renderer\ExtendedTeam</item>
                         <item name="label" xsi:type="string">B2b Address Field</item>
                     </item>
                     <item name="text_field" xsi:type="array">
                         <item name="css" xsi:type="string">require-entry</item>
                         <item name="label" xsi:type="string">Abstract Entity Attribute</item>
                      /item>
                 </argument>
            </arguments>
         </virtualType>`
         The first 'address' item - show select with option, the second is simple text field
  
### VERSION 1.14.0
   1. Ability to pass email template that needs new templates variables (see readme file for example)
   `<type name="Digidirect\Utilities\Preference\Magento\Framework\Mail\Template\TransportBuilder">
       <arguments>
           <argument name="customTemplateVars" xsi:type="array">
               <item name="example_1" xsi:type="object">Digidirect\Utilities\Model\CustomTemplateVarsInterface</item>
               <item name="example_2" xsi:type="array">
                   <item name="vars_model" xsi:type="object">Digidirect\Utilities\Model\CustomTemplateVarsInterface</item>
                   <item name="template_code" xsi:type="string">template_code_from_email_template_table</item>
               </item>
           </argument>
       </arguments>
   </type>`
   Where:
   _example_1_: add new vars to all email templates
   _example_2_: add new vars only to template_code_from_email_template_table email template
   
   2. Ability to get any custom variables or config using Digidirect Utilities helper
   `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getCustomVariable('custom_variable_code'); ?>`
   `<?php echo $this->helper('Digidirect\Utilities\Helper\Data')->getConfig('some/config/path'); ?>`

### VERSION 1.15.1
``?isAjax=1`` -- Ability to use this param for any POST requests to Magento as Ajax query.

### VERSION 1.16.0

    1. New UI Ajax AutoComplete Dropdown component. How to use it:
    
        <field name="abstract_entity_id">
            <argument name="data" xsi:type="array">
                <item name="config" xsi:type="array">
                    <item name="component" xsi:type="string">Digidirect_Utilities/js/form/element/ajax-auto-complete</item>
                    <item name="url" xsi:type="url" path="digidirect_advancedpricing/advancedpricing/entitySearch"/>
                    <item name="idAttribute" xsi:type="string">entity_id</item>
                    <item name="valueAttribute" xsi:type="string">name</item>
                    <item name="label" xsi:type="string" translate="true">Entity Name</item>
                    <item name="visible" xsi:type="boolean">true</item>
                    <item name="dataType" xsi:type="string">text</item>
                    <item name="formElement" xsi:type="string">input</item>
                    <item name="dataScope" xsi:type="string">abstract_entity_id</item>
                    <item name="validation" xsi:type="array">
                        <item name="required-entry" xsi:type="boolean">true</item>
                    </item>
                    <item name="sortOrder" xsi:type="number">20</item>
                    <item name="noticeTitle" xsi:type="string" translate="true"><![CDATA[Change it?]]></item>
                    <item name="noticeModal" xsi:type="string" translate="true"><![CDATA[Do you want to proceed?]]></item>
                    <item name="selectedEntityAttribute" xsi:type="string">abstract_entity_name</item>
                    <item name="entityIdAttribute" xsi:type="string">entity_id</item>
                    <item name="entityNameAttribute" xsi:type="string">name</item>
                </item>
            </argument>
        </field>
        
        
### VERSION 1.16.1
   1. Added ability to get last order object on checkout success page. Default Magento allows only get last order id 
   
### VERSION 1.16.3
   1. Added CMS Helper. If you have non-preprocessed wysiwyg content feel free to use it
   2. Added Store Helper. You can get current Store Information:
      $store = $this->helper(Digidirect\Utililties\Helper\Frontend\Store)->getCurrentStore();
      echo $store->getName()
   3. Added ability to simplify and reuse install/upgrade scripts.
      Have a look at Setup directory and you will find appropriate class for you.  All you need
      is to extend class you need and configure callback/version mapping  and write callbacks

### VERSION 1.16.8
   1. Block that prints javascript with enabled modules array is cached from now.
   

### VERSION 1.16.9
   1. Add method in helper for replacing symbols which have utf8mb4 encoding - replace4byte($string, $replacement = '').

### VERSION 1.17.0
   1. As a developer, I want to see theme switcher via store view
   
### VERSION 1.18.0
   1. As admin, I want to be able to upload and insert a hyperlink to a file using WYSIWYG Editor
   
### VERSION 1.18.1
   1. Added class \Digidirect\Utilities\Model\Checkout\Cart\Add\MessageManager to solve the conflict of message customisations. You can use this class to change the text of default message (example: "Digidirect_Collect") or add additional logic (example: "Digidirect_ExtendedCart").

### VERSION 1.19.0
   1. Compatibility with M2.3
