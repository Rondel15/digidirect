<?php

namespace Digidirect\FreeGift\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelper;
use Magento\Quote\Model\Quote\Item as QuoteItem;

/**
 * Class Data
 * @package Digidirect\FreeGift\Helper
 */
class Data extends AbstractHelper
{
    const REGISTRY_HIDE_HIDDEN_FREE_GIFT_ORDER_ITEMS = '_hide_hidden_free_gift_order_items';
    const REGISTRY_HIDE_FREE_GIFT_ORDER_ITEMS = '_hide_free_gift_order_items';

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_cartItem;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Digidirect\FreeGift\Model\Cart\Item $cartItem
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Digidirect\FreeGift\Model\Cart\Item $cartItem
    ) {
        $this->_cartItem = $cartItem;
        parent::__construct($context);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item|\Magento\Sales\Model\Order\Item $item
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isFreeGiftItem($item)
    {
        return $this->_cartItem->isFreeGiftItem($item);
    }

    /**
     * @param QuoteItem $item
     * @return bool
     */
    public function isHiddenForCustomerGiftItem(QuoteItem $item)
    {
        return $this->_cartItem->isHiddenForCustomerGiftItem($item);
    }
}
