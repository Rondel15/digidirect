<?php

namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount;

class RestrictCheckout extends \Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount
{
    const SIMPLE_ACTION = 'restrict_checkout_action';
    const ERROR_CODE = 'restrict_checkout';

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
