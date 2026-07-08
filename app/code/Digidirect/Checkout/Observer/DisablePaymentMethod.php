<?php

namespace Digidirect\Checkout\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;

class DisablePaymentMethod implements ObserverInterface
{
    protected $checkoutSession;

    public function __construct(
        CheckoutSession $checkoutSession,
        \Digidirect\SellerShipping\Helper\Data $helperData
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->helperData = $helperData;
    }

    public function execute(Observer $observer)
    {
        if($observer->getEvent()->getMethodInstance()->getCode() == "zippayment" && $this->helperData->hasMarketplacerSeller()){
            $checkResult = $observer->getEvent()->getResult();
            $checkResult->setData('is_available', false);
        }
    }
}