<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount;

class IncreaseItemPrice extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    const SIMPLE_ACTION = 'increase_item_price';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return static $this
     */
    public function calculate($rule, $item, $qty)
    {
        return $this->discountFactory->create();
    }
}
