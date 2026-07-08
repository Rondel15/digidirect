<?php

namespace Digidirect\FreeGift\Model;

use Magento\Framework\Model\AbstractModel;
use Digidirect\FreeGift\Api\Data\RuleInterface;

class Rule extends AbstractModel implements RuleInterface
{
    /**
     * Set resource model and Id field name
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Digidirect\FreeGift\Model\ResourceModel\Rule');
        $this->setIdFieldName(self::FIELD_ID);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->getData(self::FIELD_ID);
    }

    /**
     * @return int
     */
    public function getType()
    {
        return (int)$this->getData(self::FIELD_TYPE);
    }

    /**
     * @return string
     */
    public function getSku()
    {
        return $this->getData(self::FIELD_SKU);
    }

    /**
     * @return array
     */
    public function getSkuArray()
    {
        $sku = $this->getSku();
        $skuArray = $sku ? explode(',', $sku) : [];
        $skuArray = array_filter($skuArray, function ($v) {
            return strlen(trim($v)) > 0;
        });
        return $skuArray;
    }

    /**
     * @return int
     */
    public function isEnabledOnPdp()
    {
        return (int)$this->getData(self::FIELD_ENABLE_ON_PDP);
    }

    /**
     * @return mixed
     */
    public function getShowDescOnPdp()
    {
        return $this->getData(self::FIELD_SHOW_DESC_ON_PDP);
    }

    /**
     * @return mixed
     */
    public function getDescriptionLabel()
    {
        return $this->getData(self::FIELD_DESCRIPTION_LABEL);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(self::FIELD_DESCRIPTION);
    }

    /**
     * @return int
     */
    public function isHiddenForCustomer()
    {
        return (int)$this->getData(self::FIELD_IS_HIDDEN_FOR_CUSTOMER);
    }

    /**
     * @return string|mixed
     */
    public function getCartMessage()
    {
        return $this->getData(self::FIELD_CART_MESSAGE);
    }

    /**
     * @return string|mixed
     */
    public function getPrefix()
    {
        return $this->getData(self::FIELD_PREFIX);
    }
}
