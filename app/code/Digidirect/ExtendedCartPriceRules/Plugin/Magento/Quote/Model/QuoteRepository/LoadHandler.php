<?php
namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Quote\Model\QuoteRepository;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RemoveCartItem;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository\LoadHandler as Subject;

class LoadHandler
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
     * @param CartInterface $quote
     * @return Quote
     */
    public function aroundLoad(Subject $subject, callable $proceed, CartInterface $quote)
    {
        /** @var Quote $resultQuote */
        $resultQuote = $proceed($quote);
        $this->extendedCartPriceRule->addRemoveItemRuleFlagToQuote($resultQuote);

        return $resultQuote;
    }
}
