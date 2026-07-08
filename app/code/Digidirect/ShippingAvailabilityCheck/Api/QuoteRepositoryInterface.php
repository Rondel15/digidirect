<?php

namespace Digidirect\ShippingAvailabilityCheck\Api;


interface QuoteRepositoryInterface extends \Magento\Quote\Api\CartRepositoryInterface
{
    /**
     * Get quote to get available shipping methods for product
     * via hash
     * @param string $hash
     * @param int|null $customerId
     * @return mixed
     */
    public function getForProduct($hash, $customerId);
}
