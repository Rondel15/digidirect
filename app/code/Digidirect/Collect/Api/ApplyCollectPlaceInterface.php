<?php
namespace Digidirect\Collect\Api;

use Digidirect\Collect\Api\Data\CollectPlaceInterface;

/**
 * Interface ApplyCollectStore
 * @package Digidirect\Collect\Ap
 */
interface ApplyCollectPlaceInterface
{
    /**
     * @param string $collectPlaceId
     * @param string $storageName
     * @return bool|string
     */
    public function applyCollectPlaceToAllItems($collectPlaceId, $storageName);

    /**
     * @return CollectPlaceInterface|bool
     */
    public function getSelectedSingleCollectPlace();
}
