<?php

namespace Digidirect\Feed\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Digidirect\Feed\Model\ReportFactory;

class OrderPlaceAfter implements ObserverInterface
{
    /**
     * @var ReportFactory
     */
    protected $reportFactory;

    /**
     * Constructor
     *
     * @param ReportFactory $reportFactory
     */
    public function __construct(
        ReportFactory $reportFactory
    ) {
        $this->reportFactory = $reportFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $order = $observer->getData('order');

        $this->reportFactory->create()
            ->addOrder($order);
    }
}
