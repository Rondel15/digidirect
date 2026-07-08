<?php

namespace Digidirect\CollectAbstractEntityMsi\Api\Data;

use Magento\Catalog\Api\Data\ProductInterface;

/**
 * Interface ProductTypeHandlerInterface
 * @package Digidirect\CollectAbstractEntityMsi\Api
 */
interface ProductTypeHandlerInterface
{
    /**
     * @param ProductInterface $product
     * @param array $options
     * @return array
     */
    public function process(ProductInterface $product, array $options);

    /**
     * Product type id
     *
     * @return string
     */
    public function getTypeId();
}
