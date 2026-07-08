<?php
namespace Digidirect\CollectAbstractEntity\Model;

use Digidirect\Collect\Api\CollectPlaceStockInterface;

/**
 * Class CollectPlaceStock
 * @package Digidirect\CollectAbstractEntity\Model
 */
class CollectPlaceStock implements CollectPlaceStockInterface
{
    /**
     * Get product qty by Collect Place Id
     *
     * @param string $sku
     * @param string $collectPlaceId
     * @return int
     */
    public function getProductQty($sku, $collectPlaceId)
    {
        // @todo Checking Item Qty.
        return 100;
    }
}
