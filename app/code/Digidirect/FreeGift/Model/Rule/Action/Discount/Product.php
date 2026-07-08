<?php

namespace Digidirect\FreeGift\Model\Rule\Action\Discount;

use Magento\Quote\Model\Quote;
use Magento\SalesRule\Model\Rule;

class Product extends AbstractDiscount
{
    /**
     * @param Rule $rule
     * @param Quote $quote
     * @return int
     */
    protected function _getFreeItemsQty(Rule $rule, Quote $quote)
    {
        $qty = 0;
        $amount = max(1, $rule->getDiscountAmount());
        $step = max(1, $rule->getDiscountStep());
        foreach ($quote->getAllVisibleItems() as $item) {
            if (!$item
                || $this->_giftItem->isFreeGiftItem($item)
                || !$rule->getActions()->validate($item)
                || $item->getParentItemId()
                || $item->getProduct()->getParentProductId()
            ) {
                continue;
            }

            $qty = $qty + $item->getQty();
        }

        $qty = floor($qty / $step) * $amount;
        $max = $rule->getDiscountQty();
        if ($max) {
            $qty = min($max, $qty);
        }

        return $qty;
    }
}
