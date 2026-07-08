<?php

namespace Digidirect\ShippingAvailabilityCheck\Api\Data\Quote;

/**
 * Interface CartInterface
 * @package Digidirect\ShippingAvailabilityCheck\Api\Data\Quote
 */
interface CartInterface
{
    const SHIPPING_AVAILABILITY_CHECK_HASH = 'shipping_availability_check';

    /**
     * @return mixed
     */
    public function getShippingAvailabilityCheckHash();

    /**
     * @param string $hash
     * @return mixed
     */
    public function setShippingAvailabilityCheckHash($hash);
}
