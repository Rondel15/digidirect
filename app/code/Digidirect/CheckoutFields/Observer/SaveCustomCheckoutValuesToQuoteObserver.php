<?php

namespace Digidirect\CheckoutFields\Observer;

use Digidirect\CheckoutFields\Api\QuoteFieldValueManagementInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class SaveCustomCheckoutValuesToQuoteObserver
 *
 * @package Digidirect\CheckoutFields\Observer
 */
class SaveCustomCheckoutValuesToQuoteObserver implements ObserverInterface
{
    /**
     * @var QuoteFieldValueManagementInterface
     */
    protected $quoteFieldValueManagement;

    /**
     * SaveCustomCheckoutValuesBeforePlaceOrder constructor.
     *
     * @param QuoteFieldValueManagementInterface $quoteFieldValueManagement
     */
    public function __construct(QuoteFieldValueManagementInterface $quoteFieldValueManagement)
    {
        $this->quoteFieldValueManagement = $quoteFieldValueManagement;
    }

    /**
     * Add delivery notes to quote object
     *
     * @param EventObserver $observer
     *
     * @return $this
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        if (!$order instanceof OrderInterface || !$quote instanceof CartInterface) {
            return $this;
        }

        $this->quoteFieldValueManagement->saveToQuoteFromOrder($quote, $order);

        return $this;
    }
}
