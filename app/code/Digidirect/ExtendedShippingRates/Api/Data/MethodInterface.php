<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface MethodInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const CODE = 'code';
    const CARRIER_ID = 'carrier_id';
    const ACTIVE = 'active';
    const TITLE = 'title';
    const PRICE = 'price';
    const COST = 'cost';
    const PACKAGING_WEIGHT_TYPE = 'packaging_weight_type';
    const PACKAGING_WEIGHT_VALUE = 'packaging_weight_value';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const RATES = 'rates';
    const ALTERNATIVE_TITLE = 'alternative_title';
    const ALTERNATIVE_CODE = 'alternative_code';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get code
     *
     * @return string|null
     */
    public function getCode();

    /**
     * Get carrier id
     *
     * @return string|null
     */
    public function getCarrierId();

    /**
     * Get title
     *
     * @return string|null
     */
    public function getTitle();

    /**
     * Get price
     *
     * @return string|null
     */
    public function getPrice();

    /**
     * Get cost
     *
     * @return string|null
     */
    public function getCost();

    /**
     * Get packaging weight type
     *
     * @return string|null
     */
    public function getPackagingWeightType();

    /**
     * Get packaging weight value
     *
     * @return int|null
     */
    public function getPackagingWeightValue();

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
     * Get rates
     *
     * @return array|null
     */
    public function getRates();

    /**
     * Get alternative title
     *
     * @return string|null
     */
    public function getAlternativeTitle();

    /**
     * Get alternative code
     *
     * @return string|null
     */
    public function getAlternativeCode();

    /**
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setId($id);

    /**
     * Set code
     *
     * @param string $code
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCode($code);

    /**
     * Set carrier id
     *
     * @param string $carrierId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCarrierId($carrierId);

    /**
     * Set title
     *
     * @param string $title
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setTitle($title);

    /**
     * Set price
     *
     * @param string $price
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPrice($price);

    /**
     * Set cost
     *
     * @param string $cost
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCost($cost);

    /**
     * Set packaging weight type
     *
     * @param string $packagingWeightType
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPackagingWeightType($packagingWeightType);

    /**
     * Set packaging weight value
     *
     * @param int $packagingWeightValue
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setPackagingWeightValue($packagingWeightValue);

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set rates
     *
     * @param array $rates
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setRates($rates);

    /**
     * Set alternative title
     *
     * @param string $altTitle
     * @return mixed
     */
    public function setAlternativeTitle($altTitle);

    /**
     * Set alternative code
     *
     * @param string $altCode
     * @return mixed
     */
    public function setAlternativeCode($altCode);

    /**
     * Set active
     *
     * @param int|bool $active
     * @return \Digidirect\ExtendedShippingRates\Api\Data\MethodInterface
     */
    public function setActive($active);
}
