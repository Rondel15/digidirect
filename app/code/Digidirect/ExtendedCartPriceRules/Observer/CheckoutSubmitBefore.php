<?php

namespace Digidirect\ExtendedCartPriceRules\Observer;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;

class CheckoutSubmitBefore implements ObserverInterface
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param Observer $observer
     * @throws LocalizedException
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $items = $this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION);

        if ($items) {
            throw new LocalizedException(__('Remove unavailable items from order'));
        }
    }
}
