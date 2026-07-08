<?php

namespace Digidirect\FreeGift\Model\Config\Source;

use Magento\Catalog\Model\Product;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class Attribute
 * @package Digidirect\FreeGift\Model\Config\Source
 */
class Attribute implements ArrayInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attributeFactory;

    /**
     * @var int
     */
    protected $_productEntityTypeId;

    /**
     * Attribute constructor.
     * @param \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory
     * @param \Magento\Eav\Model\Config $eavConfig
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_attributeFactory = $attributeFactory;
        $this->_productEntityTypeId = $eavConfig->getEntityType(Product::ENTITY)->getEntityTypeId();
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];
        $attributeInfo = $this->_attributeFactory->getCollection()
            ->addFieldToFilter('entity_type_id', $this->_productEntityTypeId);

        foreach ($attributeInfo as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $result[] = ['value' => $attributeCode, 'label' => $attributeCode];
            // @todo getFrontendLabel() for a 'label' ?
        }
        return $result;
    }
}
