<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\CustomerData;

use Magento\Checkout\CustomerData\ItemPool as Subject;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class BaseItem
 *
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\CustomerData
 */
class ItemPool
{
    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return mixed
     */
    public function aroundGetItemData(Subject $subject, callable $proceed, \Magento\Quote\Model\Quote\Item $item)
    {
        $result = $proceed($item);
        $result['is_remove_item_rule_applied'] = $item->getIsRemoveItemRuleApplied();

        return $result;
    }
}
