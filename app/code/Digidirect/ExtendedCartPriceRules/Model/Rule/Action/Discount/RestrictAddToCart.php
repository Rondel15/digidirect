<?php
namespace Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount;

use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;

/**
 * Class RestrictAddToCart
 * @package Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount
 */
class RestrictAddToCart extends AbstractDiscount
{
    const SIMPLE_ACTION = 'restrict_add_to_cart_action';
    const ERROR_CODE = 'restrict_add_to_cart';

    /**
     * @param \Magento\SalesRule\Model\Rule $rule
     * @param \Magento\Quote\Model\Quote\Item\AbstractItem $item
     * @param float $qty
     * @return \Magento\SalesRule\Model\Rule\Action\Discount\Data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function calculate($rule, $item, $qty)
    {
        $discountData = $this->discountFactory->create();
        return $discountData;
    }
}
