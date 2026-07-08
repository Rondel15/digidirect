# Digidirect Locator

[wiki link](https://wiki.Digidirect.com/display/LEGO/Locator)

### Description

Example of XML layout:

```xml
<referenceContainer name="content">
    <block class="Magento\Framework\View\Element\Template" name="storelocator.wrapper" template="Digidirect_Locator::list/wrapper.phtml">
        <arguments>
            <argument name="google_api_key" xsi:type="string">GOOGLE_API_KEY</argument>
        </arguments>
        <block class="Magento\Framework\View\Element\Template" name="storelocator.map" template="Digidirect_Locator::map.phtml" />
        <block class="Magento\Framework\View\Element\Template" name="storelocator.search" template="Digidirect_Locator::list/search.phtml">
            <arguments>
                <argument name="locatorName" xsi:type="string">Digidirect.locator</argument>
            </arguments>
        </block>
        <block class="Magento\Framework\View\Element\Template" name="storelocator.list" template="Digidirect_Locator::list/items.phtml">
            <arguments>
                <argument name="jsLayout" xsi:type="array">
                    <item name="types" xsi:type="array"/>
                    <item name="components" xsi:type="array">
                        <item name="locator_list" xsi:type="array">
                            <item name="component" xsi:type="string">Digidirect_Locator/js/view/list</item>
                            <item name="config" xsi:type="array">
                                <item name="template" xsi:type="string">Digidirect_Locator/result</item>
                            </item>
                            <item name="children" xsi:type="array">
                                <item name="locator_items" xsi:type="array">
                                    <item name="component" xsi:type="string">uiComponent</item>
                                    <item name="displayArea" xsi:type="string">locator_items</item>
                                    <item name="config" xsi:type="array">
                                        <item name="template" xsi:type="string">Digidirect_Locator/item/default</item>
                                    </item>
                                </item>
                                <item name="locator_pagination" xsi:type="array">
                                    <item name="component" xsi:type="string">uiComponent</item>
                                    <item name="displayArea" xsi:type="string">locator_pagination</item>
                                    <item name="config" xsi:type="array">
                                        <item name="template" xsi:type="string">Digidirect_Locator/pagination</item>
                                    </item>
                                </item>
                            </item>
                        </item>
                    </item>
                </argument>
            </arguments>
        </block>
        <block class="Magento\Framework\View\Element\Template" name="storelocator.popup" template="Digidirect_StoreLocator::popup.phtml">
            <arguments>
                <argument name="jsLayout" xsi:type="array">
                    <item name="types" xsi:type="array"/>
                    <item name="components" xsi:type="array">
                        <item name="locator_details" xsi:type="array">
                            <item name="component" xsi:type="string">uiElement</item>
                            <item name="config" xsi:type="array">
                                <item name="template" xsi:type="string">Digidirect_Locator/item/details</item>
                                <item name="tracks" xsi:type="array">
                                    <item name="location" xsi:type="boolean">true</item>
                                </item>
                            </item>
                        </item>
                    </item>
                </argument>
            </arguments>
        </block>
    </block>
</referenceContainer>
```
You should use append Google API key instead of 'GOOGLE_API_KEY'.


### VERSION 1.2.0
1. Added ability to use different API
2. Added ability to use custom coordinates getter
3. Added ability to configure if user should be redirected to store detail page or display store info in popup

### VERSION 1.2.1
1. added ability to send data from form directly

### VERSION 1.3.0
1. added ability to filter attribute/field value
