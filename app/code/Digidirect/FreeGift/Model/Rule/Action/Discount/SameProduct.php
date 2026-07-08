<?php

namespace Digidirect\FreeGift\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule;
use Magento\Quote\Model\Quote\Item;

class SameProduct extends AbstractDiscount
{
    /**
     * @param Rule $rule
     * @param Item $item
     * @param int $qty
     * @return bool
     */
    protected function _addFreeItems(Rule $rule, Item $item, $qty)
    {
        if ($this->_giftItem->isFreeGiftItem($item)) {
            return false;
        }

        $discountStep = max(1, $rule->getDiscountStep());
        $maxDiscountQty = 100000;
        if ($rule->getDiscountQty()) {
            $maxDiscountQty = intval(max(1, $rule->getDiscountQty()));
        }

        $discountAmount = max(1, $rule->getDiscountAmount());
        $qty = min(
            floor($item->getQty() / $discountStep) * $discountAmount,
            $maxDiscountQty
        );

        if ($item->getParentItemId()
            || !in_array($item['product_type'], $this->_giftCart->getAllowedProductTypes())
            || $qty < 1
        ) {
            return false;
        }

        return $this->_giftRegistry->addFreeGiftItem(
            [$item->getProduct()->getData('sku')],
            $qty,
            $rule->getId(),
            false,
            false
        );
    }
}
