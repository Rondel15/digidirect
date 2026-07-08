<?php

namespace Digidirect\ExtendedCartPriceRules\Observer;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RestrictCheckout;

class SalesQuoteLoadAfter implements ObserverInterface
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
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var Quote $quote
         * @var $error \Magento\Framework\Message\AbstractMessage
         */
        $quote = $observer->getEvent()->getData('quote');
        $errors = $this->extendedCartPriceRule->getRestrictCheckoutErrorMessages($quote);
        foreach ($errors as $ruleId => $message) {
            $quote->addErrorInfo(
                'restrict_checkout_' . $ruleId,
                'ExtendedCartPriceRules',
                RestrictCheckout::ERROR_CODE,
                $message
            );
        }
    }
}
