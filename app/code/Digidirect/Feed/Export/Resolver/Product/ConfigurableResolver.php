<?php

namespace Digidirect\Feed\Export\Resolver\Product;

use Magento\Catalog\Model\Product;
use Digidirect\Feed\Export\Resolver\ProductResolver;

class ConfigurableResolver extends ProductResolver
{
    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getAssociatedProducts($product)
    {
        /** @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable $type */
        $type = $product->getTypeInstance();

        return $type->getUsedProducts($product);
    }

    /**
     * {@inheritdoc}
     */
    public function getQty($product)
    {
        $qty = 0;
        foreach ($this->getAssociatedProducts($product) as $associatedProduct) {
            $stockItem = $this->stockRegistry->getStockItem($associatedProduct->getId());
            if ($stockItem->getIsInStock()) {
                $qty += $stockItem->getQty();
            }
        }
        return $qty;
    }
}
