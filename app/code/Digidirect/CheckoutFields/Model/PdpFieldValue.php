<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\PdpFieldValueInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PdpFieldValue
 * @package Digidirect\CheckoutFields\Model
 */
class PdpFieldValue extends AbstractModel implements PdpFieldValueInterface
{
    /**
     * Initialization
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue::class);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * @return string
     */
    public function getFieldCode()
    {
        return $this->getData(self::FIELD_CODE);
    }

    /**
     * @return string
     */
    public function getFieldValue()
    {
        return $this->getData(self::FIELD_VALUE);
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * @param int $quoteId
     * @return $this
     */
    public function setQuoteId($quoteId)
    {
        return $this->setData(self::QUOTE_ID, $quoteId);
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
     * @param string $code
     * @return $this
     */
    public function setFieldCode($code)
    {
        return $this->setData(self::FIELD_CODE, $code);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setFieldValue($value)
    {
        return $this->setData(self::FIELD_VALUE, $value);
    }
}
