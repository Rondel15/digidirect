<?php

namespace Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedType;
use Digidirect\CollectAbstractEntityMsi\Helper\ProductType as ProductTypeHelper;

/**
 * Class Grouped
 * @package Digidirect\CollectAbstractEntityMsi\Model\ProductTypeHandler
 */
class Grouped extends AbstractHandler
{
    /**
     * ProductInterface[]
     */
    protected $associatedProducts = [];

    /**
     * @param ProductInterface $product
     * @param array $options
     * @return array
     */
    public function process(ProductInterface $product, array $options)
    {
        if (empty($options)) {
            $options = $this->resolveDefaultConfig($product);
        }

        if (!isset($options[ProductTypeHelper::SUPER_GROUP])) {
            return [];
        }
        
        $skus = [];
        $childProducIds = $options[ProductTypeHelper::SUPER_GROUP];
        $childProducts = $this->getAssociatedProducts($product);
        /** @var ProductInterface $childProduct */
        foreach ($childProducts as $childProduct) {
            if (in_array($childProduct->getId(), array_keys($childProducIds))) {
                $skus[$childProduct->getSku()] = $childProducIds[$childProduct->getId()];
            }
        }
        return $skus;
    }

    /**
     * @param ProductInterface $product
     */
    protected function getAssociatedProducts(ProductInterface $product)
    {
        if (!isset($this->associatedProducts[$product->getId()])) {
            /** @var GroupedType $productType */
            $productType = $this->getProductType($product);
            $this->associatedProducts[$product->getId()] = $productType->getAssociatedProducts($product);
        }
        return $this->associatedProducts[$product->getId()];
    }

    /**
     * @param ProductInterface $product
     * @return $this|array
     */
    protected function resolveDefaultConfig(ProductInterface $product)
    {
        $defaultConfig = [ProductTypeHelper::SUPER_GROUP => []];
        $associatedProducts = $this->getAssociatedProducts($product);
        /** @var ProductInterface $child */
        foreach ($associatedProducts as $child) {
            if ((int)$child->getQty()) {
                $defaultConfig[ProductTypeHelper::SUPER_GROUP][$child->getId()] = $child->getQty();
            }
        }
        return $defaultConfig;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return GroupedType::TYPE_CODE;
    }
}
