<?php

namespace Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Digidirect\CollectAbstractEntityMsi\Helper\ProductType as ProductTypeHelper;
use Magento\Catalog\Model\Product\Type as SimpleType;

/**
 * Class Simple
 * @package Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler
 */
class Simple extends AbstractHandler
{
    /**
     * @param ProductInterface $product
     * @param array $options
     * @return array
     */
    public function process(ProductInterface $product, array $options)
    {
        if (!isset($options[ProductTypeHelper::QTY])) {
            return [];
        }
        
        return [$product->getSku() => $options[ProductTypeHelper::QTY]];
    }

    /**
     * @param ProductInterface $product
     * @return array
     */
    protected function resolveDefaultConfig(ProductInterface $product)
    {
        return [$product->getSku() => 1];
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return SimpleType::TYPE_SIMPLE;
    }
}
