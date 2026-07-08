<?php

namespace Digidirect\Feed\Export\Resolver\Product;

use Magento\Catalog\Model\Product;
use Digidirect\Feed\Export\Resolver\ProductResolver;

class GroupedResolver extends ProductResolver
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
        /** @var \Magento\GroupedProduct\Model\Product\Type\Grouped $type */
        $type = $product->getTypeInstance();

        return $type->getAssociatedProducts($product);
    }
}
