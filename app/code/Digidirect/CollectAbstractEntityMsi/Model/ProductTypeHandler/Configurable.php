<?php

namespace Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Digidirect\CollectAbstractEntityMsi\Helper\ProductType as ProductTypeHelper;

/**
 * Class Configurable
 * @package Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler
 */
class Configurable extends AbstractHandler
{
    /**
     * @param ProductInterface $product
     * @param array $options
     * @return array
     */
    public function process(ProductInterface $product, array $options)
    {
        if (!isset($options[ProductTypeHelper::SUPER_ATTRIBUTE])
            || !isset($options[ProductTypeHelper::QTY])) {
            return [];
        }
        $skus = [];
        /** @var ConfigurableType $productType */
        $productType = $this->getProductType($product);
        $childProduct = $productType->getProductByAttributes($options[ProductTypeHelper::SUPER_ATTRIBUTE], $product);
        if ($childProduct) {
            $skus[$childProduct->getSku()] = $options[ProductTypeHelper::QTY];
        }
        return $skus;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return ConfigurableType::TYPE_CODE;
    }
}
