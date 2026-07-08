<?php

namespace Digidirect\Customer\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateCustomer implements ObserverInterface {

    protected $_customerRepositoryInterface;

    public function __construct(
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $customer = $observer->getEvent()->getCustomer();
        
        $qff_number = $observer->getEvent()->getAccountController()->getRequest()->getParam("qff_number");
        $qff_lastname = $observer->getEvent()->getAccountController()->getRequest()->getParam("qff_lastname");
        
        if($qff_number != "" && $qff_lastname != ""){
            $customer->setCustomAttribute('qff_number', $qff_number);
            $customer->setCustomAttribute('qff_lastname', $qff_lastname);
            $this->_customerRepositoryInterface->save($customer);
        }
    }

}
