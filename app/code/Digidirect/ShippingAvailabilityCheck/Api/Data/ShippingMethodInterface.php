<?php

namespace Digidirect\ShippingAvailabilityCheck\Api\Data;

/**
 * Interface ShippingMethodInterface
 * @package Digidirect\ShippingAvailabilityCheck\Api\Data
 */
interface ShippingMethodInterface extends \Magento\Quote\Api\Data\ShippingMethodInterface
{
    const KEY_FORMATTED_PRICE_EXCL_TAX = 'formatted_price_excl_tax';
    const KEY_FORMATTED_PRICE_INCL_TAX = 'formatted_price_incl_tax';
    const KEY_NOT_AVAILABLE = 'not_available';

    /**
     * @return string
     */
    public function getFormattedPriceExclTax();

    /**
     * @return string
     */
    public function getFormattedPriceInclTax();

    /**
     * @return string
     */
    public function getNotAvailable();

    /**
     * @param string $formattedPriceExclTax
     * @return mixed
     */
    public function setFormattedPriceExclTax($formattedPriceExclTax);

    /**
     * @param string $formattedPriceInclTax
     * @return mixed
     */
    public function setFormattedPriceInclTax($formattedPriceInclTax);
}
