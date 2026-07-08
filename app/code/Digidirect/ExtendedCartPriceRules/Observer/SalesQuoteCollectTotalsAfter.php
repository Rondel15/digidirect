<?php

namespace Digidirect\ExtendedCartPriceRules\Observer;

use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Digidirect\ExtendedCartPriceRules\Model\Rule\Action\Discount\RestrictCheckout;

class SalesQuoteCollectTotalsAfter implements ObserverInterface
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
         */
        $quote = $observer->getEvent()->getData('quote');
        $this->extendedCartPriceRule->addRemoveItemRuleFlagToQuote($quote);

        //reset errors
        $hadRestrictCheckoutErrors = false;
        $messages = $quote->getMessages();
        foreach ($messages as $type => $message) {
            if (strpos($type, 'disable_checkout_') === 0) {
                unset($messages[$type]);
                $hadRestrictCheckoutErrors = true;
            }
        }
        $quote->setData('messages', $messages);

        $errors = $this->extendedCartPriceRule->getRestrictCheckoutErrorMessages($quote);
        if (empty($errors)) {
            if ($hadRestrictCheckoutErrors && empty($quote->getErrors())) {
                $quote->setHasError(false);
            }
            return;
        }

        //add errors
        foreach ($errors as $ruleId => $message) {
            $quote->addErrorInfo(
                'disable_checkout_' . $ruleId,
                'ExtendedCartPriceRules',
                RestrictCheckout::ERROR_CODE,
                $message
            );
        }
    }
}
