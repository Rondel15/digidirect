<?php

namespace Mbs\BestSeller\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartInterface;
use Mbs\BestSeller\InvalidQuoteItem;
use Mbs\BestSeller\Model\ProductSalesHandler;
use Mbs\BestSeller\Model\QuoteReader;
use Mbs\BestSeller\NoValidQuote;
use Mbs\BestSeller\ProductSalesAttributeMissing;

class OrderSaveAfter implements ObserverInterface
{
    /**
     * @var ProductSalesHandler
     */
    private $productSalesHandler;
    /**
     * @var QuoteReader
     */
    private $quoteReader;

    public function __construct(
        ProductSalesHandler $productSalesReader,
        QuoteReader $quoteReaderProxy
    ) {
        $this->productSalesHandler = $productSalesReader;
        $this->quoteReader = $quoteReaderProxy;
    }

    /**
     * @inheritDoc
     */
    public function execute(Observer $observer)
    {
        if ($this->quoteHasItems($observer)) {
            foreach ($this->getVisibleQuoteItems() as $item) {
                try {
                    $this->productSalesHandler->incrementSale($item);
                } catch (InvalidQuoteItem $e) {
                    // possible log
                } catch (ProductSalesAttributeMissing $e) {
                    // possible log, notification in Magento backend
                }
            }
        }
    }

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    private function getVisibleQuoteItems()
    {
        return $this->quoteReader->getVisibleQuoteItems();
    }

    /**
     * @param Observer $observer
     * @return bool
     */
    private function quoteHasItems(Observer $observer)
    {
        try {
            $quote = $this->validateQuote($observer);
            $this->quoteReader->initializeQuote($quote);
            $result = $this->quoteReader->hasQuoteItems($quote);
        } catch (NoValidQuote $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * @param Observer $observer
     * @return mixed
     * @throws NoValidQuote
     */
    private function validateQuote(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();

        if (!$quote instanceof CartInterface) {
            throw new NoValidQuote();
        }

        return $quote;
    }
}
