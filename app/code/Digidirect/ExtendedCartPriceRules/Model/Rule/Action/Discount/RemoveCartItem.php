<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount;

class RemoveCartItem extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    const SIMPLE_ACTION = 'remove_cart_item_action';
    const EXT_ATTR_INTERNAL_ALIAS = 'extension_attribute_is_remove_item_rule_applied_flag';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return static $this
     */
    public function calculate($rule, $item, $qty)
    {
        $discountData = $this->discountFactory->create();
        return $discountData;
    }
}
