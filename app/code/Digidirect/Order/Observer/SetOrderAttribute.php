<?php

namespace Digidirect\Order\Observer;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface;

class SetOrderAttribute implements \Magento\Framework\Event\ObserverInterface {

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
        $shippingAddressID = $order->getShippingAddress()->getId();
        $shipAddress = $this->repositoryAddress->get($shippingAddressID);
        if(!empty($initialShippingAddressUnitNumber))
        {
            $initialShippingAddressUnitNumber = $order->getShippingAddress()->getUnitNumber();
            $initialShippingAddressUnitNumber = str_replace("unit_number", "", $initialShippingAddressUnitNumber);
            $shippingAddressUnitNumber = str_replace("\n", "", $initialShippingAddressUnitNumber);
            $shipAddress->setUnitNumber($shippingAddressUnitNumber);
        }


        $this->repositoryAddress->save($shipAddress);

        //Set unit number for Billing Address
        $billingAddressID = $order->getBillingAddress()->getId();
        $billingAddress = $this->repositoryAddress->get($billingAddressID);
        $initialBillingAddressUnitNumber = $order->getBillingAddress()->getUnitNumber();
        if(!empty($initialBillingAddressUnitNumber))
        {
            $initialBillingAddressUnitNumber = str_replace("unit_number", "", $initialBillingAddressUnitNumber);
            $billingAddressUnitNumber = str_replace("\n", "", $initialBillingAddressUnitNumber);
            $billingAddress->setUnitNumber($billingAddressUnitNumber);
        }

        $this->repositoryAddress->save($billingAddress);

//        if ($isGuest) {
//            if (isset($_SESSION["qantasqff"]) && !empty($_SESSION["qantasqff"])) {
//
//                $qff_number = $_SESSION["qff_number"];
//                $qff_lastname = $_SESSION["qff_lastname"];
//
//                $order->setQffNumber($qff_number)->save();
//
//                $order->setQffLastname($qff_lastname)->save();
//
//                unset($_SESSION["qantasqff"]);
//            }
//
//            return $this;
//        } else {
//            $pos = strpos($customerEmail, "catch.com.au");
//
//            if ($pos !== false) {
//                $order->setQffNumber('')->save();
//
//                $order->setQffLastname('')->save();
//
//                return $this;
//            } else {
//                $customer = $this->_customerRepository->get($customerEmail);
//
//                $getQffNumber = $customer->getQffNumber();
//
//                $getQffLastName = $customer->getQffLastName();
//                if (isset($_SESSION["qff"])) {
//
//                    $order->setQffNumber($_SESSION["qff"])->save();
//
//                    $order->setQffLastname('')->save();
//                    unset($_SESSION["qff"]);
//                }
//                if ($getQffNumber == NULL && $getQffLastName == NULL) {
//
//                    $order->setQffNumber('')->save();
//
//                    $order->setQffLastname('')->save();
//
//                    return $this;
//                }
//
//                if ($getQffNumber !== NULL && $getQffLastName !== NULL) {
//
//                    $order->setQffLastname($getQffLastName)->save();
//
//                    $order->setQffNumber($getQffNumber)->save();
//
//                    return $this;
//                }
//            }
//        }
    }

}
