<?php

namespace Digidirect\ShippingAvailabilityCheck\Api\Data;

/**
 * Interface ProductDataInterface
 * @package Digidirect\ShippingAvailabilityCheck\Api\Data
 */
interface ProductDataInterface
{
    const PRODUCT_ID = 'product_id';
    const PARAMS = 'params';
    const PRODUCT_SKU = 'sku';

    /**
     * @return int|null
     */
    public function getProductId();

    /**
     * @return mixed|null
     */
    public function getParams();

    /**
     * @return mixed
     */
    public function getSku();

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId);

    /**
     * @param mixed $params
     * @return $this
     */
    public function setParams($params);

    /**
     * @param string $sku
     * @return mixed
     */
    public function setSku($sku);
}
