<?php

namespace Digidirect\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\LocalizedException;
use Digidirect\FreeGift\Model\Cart;

class CollectTotalsAfterObserver implements ObserverInterface
{
    /**
     * @var Cart
     */
    protected $_giftCart;

    /**
     * CollectTotalsAfterObserver constructor.
     *
     * @param Cart $giftCart
     */
    public function __construct(
        Cart $giftCart
    ) {
        $this->_giftCart = $giftCart;
    }

    /**
     * @param Observer $observer
     * @return void
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $this->_giftCart->collectTotals($observer->getQuote());
    }
}
