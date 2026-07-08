<?php

namespace Digidirect\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;

class FixWholeCartRuleObserver implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * FixWholeCartRuleObserver constructor.
     *
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Digidirect\FreeGift\Model\Cart\Item $giftItem
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     */
    public function __construct(
        \Magento\Checkout\Model\Cart $cart,
        \Digidirect\FreeGift\Model\Cart\Item $giftItem,
        \Digidirect\FreeGift\Model\Registry $giftRegistry
    ) {
        $this->_cart = $cart;
        $this->_giftItem = $giftItem;
        $this->_giftRegistry = $giftRegistry;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return bool
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        if (empty($quote->getAllVisibleItems())) {
            return false;
        }

        $this->_giftRegistry->reset();
        $hasNonFreeItems = false;
        foreach ($observer->getQuote()->getAllItems() as $item) {
            if (!$this->_giftItem->isFreeGiftItem($item)) {
                $hasNonFreeItems = true;
                break;
            }
        }

        if (!$hasNonFreeItems) {
            $this->_cart->truncate();
        }

        return true;
    }
}
