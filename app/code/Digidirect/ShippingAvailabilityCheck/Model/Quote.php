<?php

namespace Digidirect\ShippingAvailabilityCheck\Model;

use Digidirect\ShippingAvailabilityCheck\Api\Data\Quote\CartInterface;

/**
 * Class Quote
 * @package Digidirect\ShippingAvailabilityCheck\Model
 */
class Quote extends \Magento\Quote\Model\Quote implements CartInterface
{
    /**
     * @return mixed
     */
    public function getShippingAvailabilityCheckHash()
    {
        return $this->_getData(self::SHIPPING_AVAILABILITY_CHECK_HASH);
    }

    /**
     * @param string $hash
     * @return mixed
     */
    public function setShippingAvailabilityCheckHash($hash)
    {
        return $this->setData(self::SHIPPING_AVAILABILITY_CHECK_HASH, $hash);
    }

    /**
     * @param string $hash
     * @param int|null $customerId
     * @return $this
     */
    public function loadByShippingAvailabilityCheckHash($hash, $customerId)
    {
        $this->_getResource()->loadByShippingAvailabilityCheckHash($this, $hash, $customerId);
        $this->_afterLoad();
        return $this;
    }
}
