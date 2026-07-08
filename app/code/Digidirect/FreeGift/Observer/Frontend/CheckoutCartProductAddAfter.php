<?php

namespace Digidirect\FreeGift\Observer\Frontend;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Digidirect\FreeGift\Model\Cart\Item;

class CheckoutCartProductAddAfter implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getQuoteItem();
        $product = $observer->getProduct();
        $field = Item::FREE_GIFT_ADDED_BY_RULE_ID;
        $item->setData($field, $product->getData($field));
    }
}
