<?php
namespace Digidirect\Order\Plugin\Magento\Sales\Api;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection;

class OrderGet
{
    protected $orderExtensionFactory;
    protected $repositoryAddress;
    protected $logger;
    public function __construct(
        OrderExtensionFactory $orderExtensionFactory,
        \Magento\Sales\Model\Order\AddressRepository $repositoryAddress,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
        $this->repositoryAddress= $repositoryAddress;
        $this->logger = $logger;
    }

    public function afterGet(
        OrderRepositoryInterface $subject,
        OrderInterface $resultOrder
    ) {
        //$this->logger->info('Test order API Override!');

//        $extensionAttributes = $resultOrder->getExtensionAttributes();
//        if ($extensionAttributes && $extensionAttributes->getUnitNumber()) {
//            return $resultOrder;
//        }

        $initialShippingAddressUnitNumber = "";
         $initialShippingAddressUnitNumber = $resultOrder?->getShippingAddress()?->getUnitNumber();
         //redeploy
        $street = $resultOrder->getShippingAddress()?->getStreet();
        if(is_null($street))
        {
            $strstring = "";
        }
        else
        {
            $strstring = $street[0];
        }
        $shipAddress = $resultOrder->getShippingAddress();
        //$initialShippingAddressUnitNumber = $resultOrder->getShippingAddress()->getUnitNumber();
        if(!empty($initialShippingAddressUnitNumber))
        {
            if (str_contains($strstring, $initialShippingAddressUnitNumber)) {
                $newstreet = $strstring;//$street[0];
            }
            else
            {
                $newstreet = $initialShippingAddressUnitNumber . " ". $strstring;//$street[0];
            }

            $shipAddress->setStreet(array($newstreet));
            $this->repositoryAddress->save($shipAddress);
        }


        //$this->logger->info('Test order API Override! -'.$newstreet);
        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */

//        $orderExtension = $extensionAttributes ? $extensionAttributes : $this->orderExtensionFactory->create();
//        $orderExtension->setUnitNumber($initialShippingAddressUnitNumber);
//        $resultOrder->setExtensionAttributes($orderExtension);

        return $resultOrder;

    }

    public function afterGetList(
        OrderRepositoryInterface $subject,
        Collection $resultOrder
    ) {
        /** @var  $order */
        foreach ($resultOrder->getItems() as $order) {
            $this->afterGet($subject, $order);
        }
        return $resultOrder;
    }
}
