<?php

namespace Digidirect\AbstractGiftCard\Model;

use Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterface;

/**
 * service list class.
 */
class GiftCardServiceList implements \Digidirect\AbstractGiftCard\Api\GiftCardServiceListInterface
{
    /**
     * @var \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterfaceFactory
     */
    private $_serviceFactory;

    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    private $_helper;

    /**
     * @param \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterfaceFactory $serviceFactory
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterfaceFactory $serviceFactory,
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        $this->_serviceFactory = $serviceFactory;
        $this->_helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function getList($storeId)
    {
        $servicesCodes = array_keys($this->_helper->getGiftCardServices());

        $servicesInstances = array_map(
            function ($code) {
                return $this->_helper->getServiceInstance($code);
            },
            $servicesCodes
        );
        
        @uasort(
            $servicesInstances,
            function (ServiceInterface $a, ServiceInterface $b) use ($storeId) {
                return (int)$a->getConfigData('sort_order', $storeId) - (int)$b->getConfigData('sort_order', $storeId);
            }
        );

        $serviceList = array_map(
            function (ServiceInterface $serviceInstance) use ($storeId) {

                return $this->_serviceFactory->create([
                    'code' => (string)$serviceInstance->getCode(),
                    'title' => (string)$serviceInstance->getTitle(),
                    'storeId' => (int)$storeId,
                    'isActive' => (bool)$serviceInstance->isActive($storeId)
                ]);
            },
            $servicesInstances
        );

        return array_values($serviceList);
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveList($storeId)
    {
        $serviceList = array_filter(
            $this->getList($storeId),
            function (GiftCardServiceInterface $service) {
                return $service->isActive();
            }
        );

        return array_values($serviceList);
    }
}
