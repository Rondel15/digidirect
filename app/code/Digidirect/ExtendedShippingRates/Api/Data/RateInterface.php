<?php
namespace Digidirect\ExtendedShippingRates\Api\Data;

interface RateInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const RATE_ID = 'rate_id';
    const METHOD_ID = 'method_id';
    const PRIORITY = 'priority';
    const ACTIVE = 'active';
    const RATE_METHOD_PRICE = 'rate_method_price';
    const TITLE = 'title';
    const COUNTRY_ID = 'country_id';
    const REGION = 'region';
    const REGION_ID = 'region_id';
    const ZIP_FROM = 'zip_from';
    const ZIP_TO = 'zip_to';
    const PRICE_FROM = 'price_from';
    const PRICE_TO = 'price_to';
    const QTY_FROM = 'qty_from';
    const QTY_TO = 'qty_to';
    const WEIGHT_FROM = 'weight_from';
    const WEIGHT_TO = 'weight_to';
    const PRICE = 'price';
    const PRICE_PER_PRODUCT = 'price_per_product';
    const PRICE_PER_ITEM = 'price_per_item';
    const PRICE_PERCENT_PER_PRODUCT = 'price_percent_per_product';
    const PRICE_PERCENT_PER_ITEM = 'price_percent_per_item';
    const ITEM_PRICE_PERCENT = 'item_price_percent';
    const PRICE_PER_WEIGHT = 'price_per_weight';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get method id
     *
     * @return int|null
     */
    public function getMethodId();

    /**
     * Get priority
     *
     * @return int|null
     */
    public function getPriority();

    /**
     * Get rate method price
     *
     * @return int|null
     */
    public function getRateMethodPrice();

    /**
     * Get title
     *
     * @return int|null
     */
    public function getTitle();

    /**
     * Get country id
     *
     * @return int|null
     */
    public function getCountryId();

    /**
     * Get region
     *
     * @return int|null
     */
    public function getRegion();

    /**
     * Get region id
     *
     * @return int|null
     */
    public function getRegionId();

    /**
     * Get zip from
     *
     * @return int|null
     */
    public function getZipFrom();

    /**
     * Get zip to
     *
     * @return int|null
     */
    public function getZipTo();

    /**
     * Get price from
     *
     * @return int|null
     */
    public function getPriceFrom();

    /**
     * Get price to
     *
     * @return int|null
     */
    public function getPriceTo();

    /**
     * Get qty from
     *
     * @return int|null
     */
    public function getQtyFrom();

    /**
     * Get qty to
     *
     * @return int|null
     */
    public function getQtyTo();

    /**
     * Get weight from
     *
     * @return int|null
     */
    public function getWeightFrom();

    /**
     * Get weight to
     *
     * @return int|null
     */
    public function getWeightTo();

    /**
     * Get price
     *
     * @return int|null
     */
    public function getPrice();

    /**
     * Get price per product
     *
     * @return int|null
     */
    public function getPricePerProduct();

    /**
     * Get price per item
     *
     * @return int|null
     */
    public function getPricePerItem();

    /**
     * Get price percent per product
     *
     * @return int|null
     */
    public function getPricePercentPerProduct();

    /**
     * Get price percent per item
     *
     * @return int|null
     */
    public function getPricePercentPerItem();

    /**
     * Get item price percent
     *
     * @return int|null
     */
    public function getItemPricePercent();

    /**
     * Get price per weight
     *
     * @return int|null
     */
    public function getPricePerWeight();

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
     * Is active
     *
     * @return bool|null
     */
    public function isActive();

    /**
     * Set ID
     *
     * @param int $id
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setId($id);

    /**
     * Set method id
     *
     * @param string $methodId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setMethodId($methodId);

    /**
     * Set priority
     *
     * @param string $priority
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriority($priority);

    /**
     * Set rate method price
     *
     * @param string $rateMethodPrice
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRateMethodPrice($rateMethodPrice);

    /**
     * Set title
     *
     * @param string $title
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setTitle($title);

    /**
     * Set country id
     *
     * @param string $countryId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setCountryId($countryId);

    /**
     * Set region
     * @param string $region
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRegion($region);

    /**
     * Set region id
     *
     * @param string $regionId
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setRegionId($regionId);

    /**
     * Set zip from
     *
     * @param string $zipFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setZipFrom($zipFrom);

    /**
     * Set zip to
     *
     * @param string $zipTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setZipTo($zipTo);

    /**
     * Set price from
     *
     * @param string $priceFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriceFrom($priceFrom);

    /**
     * Set price to
     *
     * @param string $priceTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPriceTo($priceTo);

    /**
     * Set qty from
     *
     * @param string $qtyFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setQtyFrom($qtyFrom);

    /**
     * Set qty to
     *
     * @param string $qtyTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setQtyTo($qtyTo);

    /**
     * Set weight from
     *
     * @param string $weightFrom
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setWeightFrom($weightFrom);

    /**
     * Set weight to
     *
     * @param string $weightTo
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setWeightTo($weightTo);

    /**
     * Set price
     *
     * @param string $price
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPrice($price);

    /**
     * Set price per product
     *
     * @param string $pricePerProduct
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerProduct($pricePerProduct);

    /**
     * Set price per item
     *
     * @param string $pricePerItem
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerItem($pricePerItem);

    /**
     * Set price percent per product
     *
     * @param string $pricePercentPerProduct
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePercentPerProduct($pricePercentPerProduct);

    /**
     * Set price percent per item
     *
     * @param string $pricePercentPerItem
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePercentPerItem($pricePercentPerItem);

    /**
     * Set item price percent
     *
     * @param string $itemPricePercent
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setItemPricePercent($itemPricePercent);

    /**
     * Set price per weight
     *
     * @param string $pricePerWeight
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setPricePerWeight($pricePerWeight);

    /**
     * Set created at
     *
     * @param string $createdAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Set updated at
     *
     * @param string $updatedAt
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Set active
     *
     * @param int|bool $active
     * @return \Digidirect\ExtendedShippingRates\Api\Data\RateInterface
     */
    public function setActive($active);
}
