<?php

namespace Digidirect\Collect\Api;

/**
 * Interface CollectPlaceRepositoryInterface
 *
 * @package Digidirect\Collect\Api
 */
interface CollectPlaceRepositoryInterface
{
    const KEY_IS_UNAVAILABLE = '__is_unavailable';

    /**
     * Get Collect Place by Id
     *
     * @param string $id
     * @return \Digidirect\Collect\Api\Data\CollectPlaceInterface
     */
    public function getById($id);

    /**
     * Get All CollectPlaces
     *
     * @return \Digidirect\Collect\Api\Data\CollectPlaceInterface[]
     */
    public function getAll();

    /**
     * Get collect places list by SKU
     *
     * @param string $sku
     * @param int $qty
     * @return \Digidirect\Collect\Api\Data\CollectPlaceInterface[]
     */
    public function getListBySku($sku, $qty = 1);
}
