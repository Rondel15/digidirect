<?php

namespace Digidirect\Order\Observer;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class OrderPlaceBefore implements \Magento\Framework\Event\ObserverInterface {

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $_customerRepository;
    protected $customerSession;
    protected $repositoryAddress;
    protected $orderAddressInterface;
    protected $orderRepoInterface;

    /**
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    public function __construct(
            \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
            \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\File\Csv $csvProcessor,
            \Magento\Sales\Model\Order\AddressRepository $repositoryAddress,
            \Magento\Sales\Api\Data\OrderAddressInterface $orderAddressInterface
    ) {
        $this->_customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
        $this->repositoryAddress= $repositoryAddress;
        $this->_orderAddressInterface = $orderAddressInterface;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer) {

        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();

        $customerEmail = $order->getCustomerEmail();
        $isGuest = $order->getCustomerIsGuest();

        //Set unit number for Shipping Address
        $shipAddress = $order->getShippingAddress();
        $initialShippingAddressUnitNumber = $order->getShippingAddress()->getUnitNumber();
        if(!empty($initialShippingAddressUnitNumber))
        {
            $initialShippingAddressUnitNumber = str_replace("unit_number", "", $initialShippingAddressUnitNumber);
            $shippingAddressUnitNumber = str_replace("\n", "", $initialShippingAddressUnitNumber);
            $shipAddress->setUnitNumber($shippingAddressUnitNumber);
        }
        
        $this->repositoryAddress->save($shipAddress);

//        //Set unit number for Billing Address
        $billingAddress = $order->getBillingAddress();
        $initialBillingAddressUnitNumber = $order->getBillingAddress()->getUnitNumber();
        if(!empty($initialBillingAddressUnitNumber))
        {
            $initialBillingAddressUnitNumber = str_replace("unit_number", "", $initialBillingAddressUnitNumber);
            $billingAddressUnitNumber = str_replace("\n", "", $initialBillingAddressUnitNumber);

            $billingAddress->setUnitNumber($billingAddressUnitNumber);
        }
        
        $this->repositoryAddress->save($billingAddress);
    }

}
