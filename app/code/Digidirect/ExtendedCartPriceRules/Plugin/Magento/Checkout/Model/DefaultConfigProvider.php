<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Checkout\Model;

use Digidirect\ExtendedCartPriceRules\Block\RemoveCartItem\Message;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Checkout\Model\DefaultConfigProvider as Subject;

class DefaultConfigProvider
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * @var Message
     */
    protected $messageBlock;

    /**
     * DefaultConfigProvider constructor.
     *
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param Message $messageBlock
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule,
        Message $messageBlock
    ) {
        $this->extendedCartPriceRule = $extendedCartPriceRule;
        $this->messageBlock = $messageBlock;
    }

    /**
     * @param Subject $subject
     * @param array $result
     * @return array
     */
    public function afterGetConfig(Subject $subject, $result)
    {
        $quoteItems = $this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION);
        $result['totalsData']['is_remove_item_rule_applied'] = (bool)$quoteItems;
        $result['remove_item_rule_message'] = $this->messageBlock->getMessageText();

        $items = $result['totalsData']['items'] ?? [];
        if (!$items) {
            return $result;
        }

        foreach ($items as $key => $item) {
            $items[$key]['is_remove_item_rule_applied'] =
                isset($item['item_id']) && isset($quoteItems[$item['item_id']]);
        }

        $result['totalsData']['items'] = $items;

        return $result;
    }
}
