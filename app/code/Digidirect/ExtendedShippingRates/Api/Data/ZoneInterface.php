<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface ZoneInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PRIORITY = 'priority';
    const IS_ACTIVE = 'is_active';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const DEFAULT_SHIPPING_METHOD = 'default_shipping_method';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const STORE_IDS = 'store_ids';
    const ATTRIBUTE_SET = 'attribute_set';
    const COUNTRY_ID = 'country_id';
    const REGION_ID = 'region_id';
    const POSTCODE = 'postcode';
    const ZONE_ID = 'zone_id';
    const REGION = 'region';

    /**
     * Get ID
     *
     * @return string|null
     */
    public function getId();

    /**
     * Get priority
     *
     * @return string|null
     */
    public function getPriority();

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName();

    /**
     * Get description
     *
     * @return string|null
     */
    public function getDescription();

    /**
     * Get conditions serialized
     *
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Get default shipping method
     *
     * @return string|null
     */
    public function getDefaultShippingMethod();

    /**
     * Get created at
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Get updated at
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Get store ids
     *
     * @return array|null
     */
    public function getStoreIds();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * @return mixed
     */
    public function getAttributeSet();

    /**
     * @return mixed
     */
    public function getCountryId();

    /**
     * @return mixed
     */
    public function getRegionId();

    /**
     * @return string
     */
    public function getPostcode();

    /**
     * @return mixed
     */
    public function getZoneId();

    /**
     * Set ID
     *
     * @param string $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setEntityId($id);

    /**
     * Set priority
     *
     * @param string $priority
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setPriority($priority);

    /**
     * Set is active
     *
     * @param string $isActive
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setIsActive($isActive);

    /**
     * Set name
     *
     * @param string $name
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setName($name);

    /**
     * Set description
     *
     * @param string $description
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setDescription($description);

    /**
     * Set conditions serialized
     *
     * @param string $conditionsSerialized
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Set default shipping method
     *
     * @param string $defaultShippingMethod
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setDefaultShippingMethod($defaultShippingMethod);

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set store ids
     *
     * @param array $storeIds
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setStoreIds($storeIds);

    /**
     * Set attribute set
     *
     * @param int $attributeSet
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setAttributeSet($attributeSet);

    /**
     * Set country id
     *
     * @param string $countryId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setCountryId($countryId);

    /**
     * Set region id
     *
     * @param string $regionId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setRegionId($regionId);

    /**
     * Set postcode
     *
     * @param string $postcode
     * @return \Digidirect\ExtendedShippingRates\Api\Data\ZoneInterface
     */
    public function setPostcode($postcode);

    /**
     * @param string $zoneId
     * @return mixed
     */
    public function setZoneId($zoneId);
}
