<?php

namespace Digidirect\Customer\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 *  Revert Dob value in case Customer already set the Dob value before.
 */
class RetainDob implements ObserverInterface {
    /**
     * Revert Dob value in case Customer already set the Dob value before.
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var  $customer \Magento\Customer\Model\Customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getOrigData('dob') && $customer->getData('dob') !== $customer->getOrigData('dob')) {
            $customer->setData('dob', $customer->getOrigData('dob'));
        }

        return $this;
    }
}
