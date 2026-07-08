<?php

namespace Digidirect\ShippingAvailabilityCheck\Api;

/**
 * Interface CheckManagementInterface
 * @package Digidirect\ShippingAvailabilityCheck\Api
 */
interface CheckManagementInterface
{
    /**
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface $productData
     * @param int|null $customerId
     * @return \Digidirect\ShippingAvailabilityCheck\Api\Data\ShippingMethodInterface[]
     */
    public function getShippingMethodList($address, $productData, $customerId = null);

    /**
     * @param \Digidirect\ShippingAvailabilityCheck\Api\Data\ProductDataInterface[] $productData
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @param int|null $customerId
     * @return array
     */
    public function getShippingMethodListForMultipleProducts($productData, $address, $customerId = null);

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\EstimateAddressInterface $address
     * @return mixed
     */
    public function estimateByAddress($quote, \Magento\Quote\Api\Data\EstimateAddressInterface $address);
}
