<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

use Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface;
use Magento\Framework\DataObject;

/**
 * Class ProductData
 * @package Digidirect\ShippingAvailabilityCheck\Model
 */
class ProductData extends DataObject implements ProductDataInterface
{
    /**
     * @return mixed
     */
    public function getProductId()
    {
        return $this->_getData(self::PRODUCT_ID);
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->_getData(self::PARAMS);
    }

    /**
     * @return mixed
     */
    public function getSku()
    {
        return $this->_getData(self::PRODUCT_SKU);
    }

    /**
     * @param int $productId
     * @return $this
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }

    /**
     * @param mixed $params
     * @return $this
     */
    public function setParams($params)
    {
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * @param string $sku
     * @return $this
     */
    public function setSku($sku)
    {
        return $this->setData(self::PRODUCT_SKU, $sku);
    }
}
