<?php

namespace Digidirect\Feed\Export\Resolver\Product;

use Magento\Catalog\Model\Product;
use Digidirect\Feed\Export\Resolver\ProductResolver;

class BundleResolver extends ProductResolver
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
        /** @var \Magento\Bundle\Model\Product\Type $type */
        $type = $product->getTypeInstance();

        return $type->getAssociatedProducts($type);
    }
}
