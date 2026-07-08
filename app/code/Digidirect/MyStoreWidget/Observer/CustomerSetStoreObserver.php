<?php
namespace Digidirect\MyStoreWidget\Observer;

use Digidirect\MyStoreWidget\Api\MyStoreRepositoryInterface;
use Digidirect\MyStoreWidget\Helper\Config as Helper;
use Magento\Customer\Model\Address;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;

class CustomerSetStoreObserver implements ObserverInterface
{
    /**
     * @var CustomerRegistry
     */
    protected $customerRegistry;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var MyStoreRepositoryInterface
     */
    protected $myStoreRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * CustomerSetStoreObserver constructor.
     * @param MyStoreRepositoryInterface $myStoreRepository
     * @param Helper $helper
     * @param CustomerRegistry $customerRegistry
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        MyStoreRepositoryInterface $myStoreRepository,
        Helper $helper,
        CustomerRegistry $customerRegistry,
        ManagerInterface $messageManager
    ) {
        $this->helper = $helper;
        $this->myStoreRepository = $myStoreRepository;
        $this->customerRegistry = $customerRegistry;
        $this->messageManager = $messageManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        if (!$this->helper->isMyStoreFromShippingAddressEnabled()) {
            return $this;
        }

        $customer = null;
        $address = null;
        if ($observer->getCustomerAddress()) {
            /** @var Address $customerAddress */
            $customerAddress = $observer->getCustomerAddress();
            $customer = $customerAddress->getCustomer();

            if ($customer->getDefaultShipping() == $customerAddress->getId()
                || $customerAddress->getDefaultShipping()
            ) {
                $address = $customerAddress;
            }
        } else if ($observer->getModel()) {
            $customer = $observer->getModel();
        }

        if ($customer instanceof Customer) {
            if (!$this->myStoreRepository->setMyStoreByShippingAddress($customer, $address)) {
                //comment out, and monitor how it will affect checkout. clint
//                $this->messageManager->addErrorMessage(__(
//                    'Sorry, but we can\'t set up your Store using your Shipping Address.'
//                ));
            }
        }

        return $this;
    }
}
