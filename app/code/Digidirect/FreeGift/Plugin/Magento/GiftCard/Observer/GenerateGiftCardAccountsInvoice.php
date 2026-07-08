<?php

namespace Digidirect\FreeGift\Plugin\Magento\GiftCard\Observer;

use Magento\GiftCard\Observer\GenerateGiftCardAccountsInvoice as OriginalGenerateGiftCardAccounts;
use Magento\Framework\Registry;

class GenerateGiftCardAccountsInvoice
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * GenerateGiftCardAccounts constructor.
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->_registry = $registry;
    }

    /**
     * @param OriginalGenerateGiftCardAccounts $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @return mixed
     */
    public function aroundExecute(
        OriginalGenerateGiftCardAccounts $subject,
        \Closure $proceed,
        \Magento\Framework\Event\Observer $observer
    ) {
        $flag = \Digidirect\FreeGift\Plugin\Magento\Sales\Model\Order\Item::FIX_FREE_GIFT_GIFTCARD_AMOUNT_FLAG;
        $this->_registry->register($flag, true, true);
        $return = $proceed($observer);
        $this->_registry->unregister($flag);
        return $return;
    }
}
