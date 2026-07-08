<?php
namespace Digidirect\CollectAbstractEntity\Api\Data;

use Digidirect\Collect\Api\Data\CollectPlaceInterface as CollectPlaceInterfaceOriginal;

/**
 * Interface CollectPlaceInterface
 */
interface CollectPlaceInterface extends CollectPlaceInterfaceOriginal
{
    /**
     * @return string
     */
    public function getPostcode();
}
