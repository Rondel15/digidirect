<?php

namespace Digidirect\ExtendedCartPriceRules\Block\RemoveCartItem;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Framework\View\Element\Template;

class Message extends Template
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * Message constructor.
     *
     * @param Template\Context $context
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ExtendedCartPriceRule $extendedCartPriceRule,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @inheritdoc
     */
    public function toHtml()
    {
        if ($this->extendedCartPriceRule->getQuoteItemsAppliedToRuleAction(RemoveCartItem::SIMPLE_ACTION)) {
            return parent::toHtml();
        }

        return '';
    }

    /**
     * @return string
     */
    public function getMessageText()
    {
        $message = $this->getRawMessage();
        return preg_replace(
            '#{{link}}(.*){{/link}}#',
            '<a href="/extendedcartpricerules/cart/removeunavailableitems">$1</a>',
            $message
        );
    }

    /**
     * @return string
     */
    public function getRawMessage()
    {
        $rules = $this->extendedCartPriceRule->getRulesByAction(RemoveCartItem::SIMPLE_ACTION);
        $firstRule = reset($rules);
        if (!$firstRule) {
            return '';
        }

        $firstRuleData = $firstRule->__toArray();

        $message = $firstRuleData['action_message'] ?? '';
        if (!$message) {
            return '';
        }

        return $message;
    }
}
