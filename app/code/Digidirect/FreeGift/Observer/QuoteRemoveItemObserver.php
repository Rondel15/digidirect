<?php

namespace Digidirect\FreeGift\Observer;

use Magento\Framework\Event\ObserverInterface;

class QuoteRemoveItemObserver implements ObserverInterface
{
    /**
     * @var \Digidirect\FreeGift\Model\Registry
     */
    protected $_giftRegistry;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Digidirect\FreeGift\Model\Cart\Item
     */
    protected $_giftItem;

    /**
     * QuoteRemoveItemObserver constructor.
     *
     * @param \Digidirect\FreeGift\Model\Cart\Item $giftItem
     * @param \Digidirect\FreeGift\Model\Registry $giftRegistry
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Digidirect\FreeGift\Model\Cart\Item $giftItem,
        \Digidirect\FreeGift\Model\Registry $giftRegistry,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_giftItem = $giftItem;
        $this->_giftRegistry = $giftRegistry;
        $this->_request = $request;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getQuoteItem();

        // Additional request checks to mark only explicitly deleted items
        if ($this->_request->getActionName() == 'delete'
            && $this->_request->getParam('id') == $item->getId()
        ) {
            if (!$item->getParentId()
                && $this->_giftItem->isFreeGiftItem($item)
            ) {
                $this->_giftRegistry->deleteProduct(
                    $item->getProduct()->getData('sku')
                );
            }
        }
    }
}
