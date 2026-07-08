Digidirect Abstract Entity
=====================

[wiki link](https://wiki.digidirect.com/display/LEGO/Abstract+Entity)

The extension allows creating new entities in the backend of Magento.

To add the fields with abstract entity to the config file for which you want a field with dynamic attributes 
for this entity, it is necessary to use this frontend classes:
<frontend_class>digidirect_abstract_entities</frontend_class>
<frontend_class>digidirect_abstract_entities_attributes</frontend_class>

Example in system.xml
<field id="abstract_entity" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="0" showInStore="0">
    <label>Abstract Entity</label>
    <source_model>Digidirect\AbstractEntity\Model\Config\Source\AttributeSet</source_model>
    <frontend_class>digidirect_abstract_entities</frontend_class>
</field>
<field id="test_attribute" translate="label" type="select" sortOrder="20" showInDefault="1" showInWebsite="0" showInStore="0">
    <label>Entity's Attribute</label>
    <frontend_class>digidirect_abstract_entities_attributes</frontend_class>
</field>

If you want to filter values in select by type you need to add a class with type needed.
If you do not add a class, all attributes for the abstract entity will return.

Templates for class:
digidirect_abstract_entities_attribute_[backend_type]  - will return all attributes with this backend_type
digidirect_abstract_entities_attribute_[backend_type]_[frontend_input] - - will return all attributes with this backend_type and frontend input

Class examples:
digidirect_abstract_entities_attribute_int
digidirect_abstract_entities_attribute_int_select
digidirect_abstract_entities_attribute_int_text
digidirect_abstract_entities_attribute_varchar_text
digidirect_abstract_entities_attribute_varchar
digidirect_abstract_entities_attribute_varchar_image
digidirect_abstract_entities_attribute_text
etc.

### VERSION 2.6.4

1. Added new parameter to search method that allows not to use fulltext search

### VERSION 2.7.0

1. As an admin, I want Abstract Entity module to have a REST API call to get the AE’s data by ID

```/V1/abstractentity/:id``` - get entity data by id. Method is GET
```/V1/abstractentity/:id/attributes/:attributes``` - get entity data by id and attributes. Method is GET


### VERSION 3.0.0

1. Reindex process significantly changed.
   Now reindex process generates separate table for each store for each entity. For example you have 3 store  views
   and abstract entity name is "Bicycle". After reindex separate tables wil be created:
   digidirect_abstractentity_index_bicycle_1
   digidirect_abstractentity_index_bicycle_2
   digidirect_abstractentity_index_bicycle_3
   
   Then when you go to store it checks current store and fetches information from particular table 
   
   If you set index to "update on save" and you save  AE for default store it will reindex all stores.
   If you save information for particular store it will reindex only one table

### VERSION 3.2.0
REST API:
```/V1/abstractentity/type/:attributeSetName - GET```
```/V1/abstractentity/type/:attributeSetName/:id - GET```
```/V1/abstractentity/type/:attributeSetName - POST```
```/V1/abstractentity/type/:attributeSetName/:id - PUT```
```/V1/abstractentity/type/:attributeSetName/:id - DELETE```