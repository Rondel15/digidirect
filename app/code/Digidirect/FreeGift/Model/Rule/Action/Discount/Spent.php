<?php

namespace Digidirect\FreeGift\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule;
use Magento\Quote\Model\Quote;

class Spent extends AbstractDiscount
{
    /**
     * @param Rule $rule
     * @param Quote $quote
     * @return int
     */
    protected function _getFreeItemsQty(Rule $rule, Quote $quote)
    {
        $amount = max(1, $rule->getDiscountAmount());
        $step = $rule->getDiscountStep();

        if (!$step) {
            return 0;
        }

        $subtotal = 0;
        foreach ($quote->getAllVisibleItems() as $item) {
            if ($this->_giftItem->isFreeGiftItem($item)
                || !$rule->getActions()->validate($item)
                || $item->getParentItemId()
                || $item->getProduct()->getParentProductId()
            ) {
                continue;
            }

            $subtotal += $item->getBaseRowTotal();
        }

        $qty = floor($subtotal / $step) * $amount;
        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }
}
