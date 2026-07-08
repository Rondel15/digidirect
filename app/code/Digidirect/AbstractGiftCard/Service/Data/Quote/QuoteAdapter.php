<?php

namespace Digidirect\AbstractGiftCard\Service\Data\Quote;

use Magento\Framework\Exception\LocalizedException;
use Digidirect\AbstractGiftCard\Service\Data\OrderAdapterInterface;
use Magento\Quote\Api\Data\CartInterface;
use Digidirect\AbstractGiftCard\Service\Data\AddressAdapterInterface;

/**
 * Class QuoteAdapter
 */
class QuoteAdapter implements OrderAdapterInterface
{
    /**
     * @var CartInterface
     */
    private $_quote;

    /**
     * @var AddressAdapter
     */
    private $_addressAdapterFactory;

    /**
     * @param CartInterface $quote
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        CartInterface $quote,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->_quote = $quote;
        $this->_addressAdapterFactory = $addressAdapterFactory;
    }

    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_quote->getCurrency()->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->_quote->getReservedOrderId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_quote->getCustomer()->getId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress()
    {
        if ($this->_quote->getBillingAddress()) {
            return $this->_addressAdapterFactory->create(
                ['address' => $this->_quote->getBillingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns shipping address
     *
     * @return AddressAdapterInterface|null
     */
    public function getShippingAddress()
    {
        if ($this->_quote->getShippingAddress()) {
            return $this->_addressAdapterFactory->create(
                ['address' => $this->_quote->getShippingAddress()]
            );
        }

        return null;
    }

    /**
     * Returns order store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->_quote->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_quote->getId();
    }

    /**
     * Returns order grand total amount
     *
     * @return null
     */
    public function getGrandTotalAmount()
    {
        return null;
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Quote\Api\Data\CartItemInterface[]|null
     */
    public function getItems()
    {
        return $this->_quote->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return null;
    }
}
