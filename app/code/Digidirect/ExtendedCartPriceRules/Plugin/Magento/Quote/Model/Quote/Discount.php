<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model\Quote;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\SalesRule\Model\Quote\Discount as Subject;

class Discount
{
    /**
     * @var ExtendedCartPriceRule
     */
    protected $extendedCartPriceRule;

    /**
     * LoadHandler constructor.
     *
     * @param ExtendedCartPriceRule $extendedCartPriceRule
     */
    public function __construct(
        ExtendedCartPriceRule $extendedCartPriceRule
    ) {
        $this->extendedCartPriceRule = $extendedCartPriceRule;
    }

    /**
     * @param Subject $subject
     * @param callable $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return \Magento\SalesRule\Model\Quote\Discount
     */
    public function aroundCollect(
        Subject $subject,
        callable $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $result = $proceed($quote, $shippingAssignment, $total);
        $this->extendedCartPriceRule->addRemoveItemRuleFlagToQuote($quote);

        return $result;
    }
}
