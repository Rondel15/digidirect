<?php

namespace Digidirect\Collect\Api;

/**
 * Interface CollectPlaceStockInterface
 *
 * @package Digidirect\Collect\Api
 */
interface CollectPlaceStockInterface
{
    /**
     * Get product qty by Collect Place Id
     *
     * @param string $sku
     * @param string $collectPlaceId
     * @return int
     */
    public function getProductQty($sku, $collectPlaceId);
}
