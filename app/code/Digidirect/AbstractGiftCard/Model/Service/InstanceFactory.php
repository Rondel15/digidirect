<?php

namespace Digidirect\AbstractGiftCard\Model\Service;

use Digidirect\AbstractGiftCard\Api\Data\GiftCardServiceInterface;

/**
 * service instance factory.
 */
class InstanceFactory
{
    /**
     * @var \Digidirect\AbstractGiftCard\Helper\Data
     */
    private $_helper;

    /**
     * @param \Digidirect\AbstractGiftCard\Helper\Data $helper
     */
    public function __construct(
        \Digidirect\AbstractGiftCard\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Create service instance.
     *
     * @param GiftCardServiceInterface $giftCardService
     * @return \Digidirect\AbstractGiftCard\Model\ServiceInterface
     */
    public function create(GiftCardServiceInterface $giftCardService)
    {
        $serviceInstance = $this->_helper->getServiceInstance($giftCardService->getCode());
        $serviceInstance->setStore($giftCardService->getStoreId());

        return $serviceInstance;
    }
}
