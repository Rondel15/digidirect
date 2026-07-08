<?php

namespace Digidirect\ShippingAvailabilityCheck\Model\Cart;

use Magento\Quote\Model\Cart\ShippingMethod as CartShippingMethod;
use Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterface;

class ShippingMethod extends CartShippingMethod implements ShippingMethodInterface
{
    /**
     * @return string
     */
    public function getFormattedPriceExclTax()
    {
        return $this->_get(self::KEY_FORMATTED_PRICE_EXCL_TAX);
    }

    /**
     * @return string
     */
    public function getFormattedPriceInclTax()
    {
        return $this->_get(self::KEY_FORMATTED_PRICE_INCL_TAX);
    }

    /**
     * @return string|null
     */
    public function getNotAvailable()
    {
        return $this->_get(self::KEY_NOT_AVAILABLE);
    }

    /**
     * @param string $formattedPriceExclTax
     * @return $this
     */
    public function setFormattedPriceExclTax($formattedPriceExclTax)
    {
        return $this->setData(self::KEY_FORMATTED_PRICE_EXCL_TAX, $formattedPriceExclTax);
    }

    /**
     * @param string $formattedPriceInclTax
     * @return $this
     */
    public function setFormattedPriceInclTax($formattedPriceInclTax)
    {
        return $this->setData(self::KEY_FORMATTED_PRICE_INCL_TAX, $formattedPriceInclTax);
    }

    /**
     * @param string $key
     * @return array
     */
    public function getData($key = null)
    {
        if ($key) {
            return $this->_data[$key];
        }
        return $this->_data;
    }
}
