<?php

namespace Digidirect\CheckoutFields\Observer;

use Digidirect\CheckoutFields\Api\OrderFieldValueRepositoryInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Class SaveCustomCheckoutValuesToOrderObserver
 *
 * @package Digidirect\CheckoutFields\Observer
 */
class SaveCustomCheckoutValuesToOrderObserver implements ObserverInterface
{
    /**
     * @var OrderFieldValueRepositoryInterface
     */
    protected $orderFieldValueRepository;

    /**
     * SaveCustomCheckoutValuesToOrderObserver constructor.
     *
     * @param OrderFieldValueRepositoryInterface $orderFieldValueRepository
     */
    public function __construct(OrderFieldValueRepositoryInterface $orderFieldValueRepository)
    {
        $this->orderFieldValueRepository = $orderFieldValueRepository;
    }

    /**
     * Add delivery notes from quote to order object
     *
     * @param EventObserver $observer
     *
     * @return $this
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        if (!$order instanceof OrderInterface || !$quote instanceof CartInterface) {
            return $this;
        }

        $this->orderFieldValueRepository->moveCheckoutFieldsToOrderFromQuote($quote, $order);

        return $this;
    }
}
