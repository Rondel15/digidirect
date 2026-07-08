<?php

namespace Digidirect\FreeGift\Plugin\Magento\Quote\Model;

use Magento\Quote\Model\Quote as OriginalQuote;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class Quote
{
    /**
     * @var DataHelper
     */
    protected $_dataHelper;

    /**
     * Collection constructor.
     * @param DataHelper $dataHelper
     */
    public function __construct(DataHelper $dataHelper)
    {
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param OriginalQuote $quote
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllVisibleItems(OriginalQuote $quote, $result)
    {
        foreach ($result as $k => $quoteItem) {
            if ($quoteItem->getParentItem()) {
                continue;
            }
            if ($this->_dataHelper->isHiddenForCustomerGiftItem($quoteItem)) {
                unset($result[$k]);
            }
        }
        $result = array_values($result);

        return $result;
    }
}
