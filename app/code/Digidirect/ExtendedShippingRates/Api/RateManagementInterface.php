<?php
namespace Digidirect\ExtendedShippingRates\Api;

interface RateManagementInterface
{
    /**
     * Remove disabled shipping methods from collection
     *
     * @param \Magento\Quote\Model\Quote\Address $shippingAddress
     * @return $this
     */
    public function removeDisabledMethods($shippingAddress);

    /**
     * @param \Magento\Quote\Model\Quote\Address $shippingAddress
     * @return array
     */
    public function getDisabledShippingMethods($shippingAddress);
}
