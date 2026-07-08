<?php

namespace Digidirect\ExtendedShippingRates\Observer\Sales\Quote\Address;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CollectTotalsAfter
 *
 * @package Digidirect\ExtendedShippingRates\Observer\Sales\Quote\Address
 */
class CollectTotalsAfter implements ObserverInterface
{
    /**
     * @var \Digidirect\ExtendedShippingRates\Api\RateManagementInterface
     */
    protected $rateManagement;

    /**
     * GetAllRates constructor.
     *
     * @param \Digidirect\ExtendedShippingRates\Api\RateManagementInterface $rateManagement
     */
    public function __construct(\Digidirect\ExtendedShippingRates\Api\RateManagementInterface $rateManagement)
    {
        $this->rateManagement = $rateManagement;
    }

    /**
     * Remove disabled shipping methods from collection
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();

        /** @var \Magento\Quote\Model\ShippingAssignment $shippingAssignment */
        $shippingAssignment = $event->getShippingAssignment();

        /** @var \Magento\Quote\Model\Shipping $shipping */
        $shipping = $shippingAssignment->getShipping();

        /** @var \Magento\Quote\Model\Quote\Address $shippingAddress */
        $shippingAddress = $shipping->getAddress();

        $this->rateManagement->removeDisabledMethods($shippingAddress);

        return $this;
    }
}
