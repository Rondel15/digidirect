<?php
namespace Digidirect\Collect\Model;

use Digidirect\Collect\Api\ApplyDeliveryInterface;

/**
 * Class ApplyDelivery
 * @package Digidirect\Collect\Model
 */
class ApplyDelivery extends AbstractApplyShippingVariation implements ApplyDeliveryInterface
{
    /**
     * @return bool
     */
    public function applyDeliveryToAllItems()
    {
        return !empty($this->applyCollectParamsToAllItems());
    }
}
