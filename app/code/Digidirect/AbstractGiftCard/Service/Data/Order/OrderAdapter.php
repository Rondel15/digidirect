<?php

namespace Digidirect\AbstractGiftCard\Service\Data\Order;

use Digidirect\AbstractGiftCard\Service\Data\AddressAdapterInterface;
use Digidirect\AbstractGiftCard\Service\Data\OrderAdapterInterface;
use Magento\Sales\Model\Order;

/**
 * Class OrderAdapter
 */
class OrderAdapter implements OrderAdapterInterface
{
    /**
     * @var Order
     */
    private $_order;

    /**
     * @var AddressAdapter
     */
    private $_addressAdapterFactory;

    /**
     * @param Order $order
     * @param AddressAdapterFactory $addressAdapterFactory
     */
    public function __construct(
        Order $order,
        AddressAdapterFactory $addressAdapterFactory
    ) {
        $this->_order = $order;
        $this->_addressAdapterFactory = $addressAdapterFactory;
    }

    /**
     * Returns currency code
     *
     * @return string
     */
    public function getCurrencyCode()
    {
        return $this->_order->getBaseCurrencyCode();
    }

    /**
     * Returns order increment id
     *
     * @return string
     */
    public function getOrderIncrementId()
    {
        return $this->_order->getIncrementId();
    }

    /**
     * Returns customer ID
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->_order->getCustomerId();
    }

    /**
     * Returns billing address
     *
     * @return AddressAdapterInterface|null
     */
    public function getBillingAddress()
    {
        if ($this->_order->getBillingAddress()) {
            return $this->_addressAdapterFactory->create(
                ['address' => $this->_order->getBillingAddress()]
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
        if ($this->_order->getShippingAddress()) {
            return $this->_addressAdapterFactory->create(
                ['address' => $this->_order->getShippingAddress()]
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
        return $this->_order->getStoreId();
    }

    /**
     * Returns order id
     *
     * @return int
     */
    public function getId()
    {
        return $this->_order->getEntityId();
    }

    /**
     * Returns order grand total amount
     *
     * @return float|null
     */
    public function getGrandTotalAmount()
    {
        return $this->_order->getBaseGrandTotal();
    }

    /**
     * Returns list of line items in the cart
     *
     * @return \Magento\Sales\Api\Data\OrderItemInterface[]
     */
    public function getItems()
    {
        return $this->_order->getItems();
    }

    /**
     * Gets the remote IP address for the order.
     *
     * @return string|null Remote IP address.
     */
    public function getRemoteIp()
    {
        return $this->_order->getRemoteIp();
    }
}
