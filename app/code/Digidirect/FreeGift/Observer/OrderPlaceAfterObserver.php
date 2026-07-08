<?php

namespace Digidirect\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Digidirect\FreeGift\Helper;
use Digidirect\FreeGift\Model\Cart\Item;

class OrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var Helper\Config
     */
    protected $_configHelper;

    /**
     * @var Item
     */
    protected $_cartItem;

    /**
     * OrderPlaceAfterObserver constructor.
     *
     * @param Helper\Config $configHelper
     * @param Item $cartItem
     */
    public function __construct(
        Helper\Config $configHelper,
        Item $cartItem
    ) {
        $this->_configHelper = $configHelper;
        $this->_cartItem = $cartItem;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        $prefix = $this->_configHelper->getFreeItemPrefix();
        foreach ($order->getAllItems() as $item) {
            $buyRequest = $item->getBuyRequest();
            if (isset($buyRequest['options'][Item::FREE_GIFT_KEY])) {
                $rulePrefix = $this->_cartItem->getOrderItemPrefix($item, true, false);
                if ($rulePrefix !== null) {
                    $prefix = $rulePrefix;
                }
                if ($prefix) {
                    $item->setName($prefix . ' ' . $item->getName());
                }
            }
        }
    }
}
