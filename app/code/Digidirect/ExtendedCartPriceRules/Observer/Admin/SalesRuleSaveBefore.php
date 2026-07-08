<?php
namespace Digidirect\ExtendedCartPriceRules\Observer\Admin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

class SalesRuleSaveBefore implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\SalesRule\Model\Rule $salesRule
         */
        $salesRule = $observer->getEvent()->getData('rule');
        $paymentMethodLimit = $salesRule->getPaymentMethodLimit();
        if (is_array($paymentMethodLimit)) {
            $salesRule->setPaymentMethodLimit(implode(',', $paymentMethodLimit));
        }
    }
}
