<?php

namespace Mbs\BestSeller\Model;

use Magento\Quote\Api\Data\CartInterface;

class QuoteReader
{
    /**
     * @var \Magento\Quote\Model\Quote\Item[]
     */
    private $allVisibleItems;

    /**
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    public function getVisibleQuoteItems()
    {
        return $this->allVisibleItems;
    }

    /**
     * @param CartInterface $quote
     * @return bool
     */
    public function hasQuoteItems(CartInterface $quote)
    {
        return count($this->allVisibleItems) > 0;
    }

    public function initializeQuote($quote)
    {
        $this->allVisibleItems = $quote->getAllVisibleItems();
    }
}
