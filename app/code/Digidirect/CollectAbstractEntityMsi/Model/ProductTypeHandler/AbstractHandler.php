<?php

namespace Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Digidirect\CollectAbstractEntityMsi\Api\Data\ProductTypeHandlerInterface;

/**
 * Class Configurable
 * @package Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler
 */
abstract class AbstractHandler implements ProductTypeHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @return mixed
     */
    protected function getProductType(ProductInterface $product)
    {
        $productType = $product->getTypeInstance();
        return $productType;
    }

    /**
     * @param Product $product
     * @return array
     */
    protected function resolveDefaultConfig(ProductInterface $product)
    {
        return [];
    }
}
