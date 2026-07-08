<?php
namespace Digidirect\ExtendedCartPriceRules\Helper;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Framework\App\Helper\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class CartRule extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * CartRule constructor.
     *
     * @param Context $context
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     */
    public function __construct(
        Context $context,
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        parent::__construct($context);
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param AbstractItem $item
     * @return bool
     */
    public function isRemoveCartRuleApplied(AbstractItem $item)
    {
        $items = $this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION);

        return isset($items[$item->getId()]);
    }

    /**
     * @return bool
     */
    public function isCartHasUnavailableItems()
    {
        $items = $this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION);

        return (bool)$items;
    }
}
