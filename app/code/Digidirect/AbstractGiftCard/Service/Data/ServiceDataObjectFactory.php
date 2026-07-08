<?php

namespace Digidirect\AbstractGiftCard\Service\Data;

use Magento\Framework\ObjectManagerInterface;
use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use \Magento\Checkout\Model\Session;

class ServiceDataObjectFactory implements ServiceDataObjectFactoryInterface
{
    /**
     * Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * Factory constructor
     *
     * @param ObjectManagerInterface $objectManager
     * @param Order\OrderAdapterFactory $orderAdapterFactory
     * @param Quote\QuoteAdapterFactory $quoteAdapterFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Creates Service Data Object
     *
     * @param ServiceInterface $service
     * @return ServiceDataObjectInterface
     */
    public function create(ServiceInterface $service)
    {
        /**
         * @TODO add order to serviceDataObject
         */
        if ($this->_checkoutSession->getQuoteId()) {
            $data['quote'] = $this->_checkoutSession->getQuote();
        }
        $data['service'] = $service;

        return $this->_objectManager->create(
            \Digidirect\AbstractGiftCard\Service\Data\ServiceDataObject::class,
            $data
        );
    }
}
