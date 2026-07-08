<?php

namespace Digidirect\AbstractGiftCard\Service\Data;

use Digidirect\AbstractGiftCard\Model\ServiceInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\CartInterface;

class ServiceDataObject implements ServiceDataObjectInterface
{
    /**
     * @var OrderAdapterInterface
     */
    private $_order;

    /**
     * @var ServiceInterface
     */
    private $_service;

    /**
     * @var string
     */
    private $_checkResponse;

    /**
     * @var CartInterface
     */
    protected $quote;

    /**
     * ServiceDataObject constructor.
     * @param ServiceInterface $service
     * @param CartInterface|null $quote
     */
    public function __construct(
        ServiceInterface $service,
        CartInterface $quote = null
    ) {
        $this->_service = $service;
        $this->quote = $quote;
    }

    /**
     * @return CartInterface
     */
    public function getQuote()
    {
        return $this->quote;
    }

    /**
     * Returns order
     *
     * @return OrderAdapterInterface
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Returns service
     *
     * @return ServiceInterface
     */
    public function getService()
    {
        return $this->_service;
    }

    /**
     * @param array $response
     * @return $this
     */
    public function setCheckResponse(array $response = [])
    {
        $this->_checkResponse = $response;
        return $this;
    }

    /**
     * @return string
     */
    public function getCheckResponse()
    {
        return $this->_checkResponse;
    }
}
