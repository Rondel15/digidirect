<?php

namespace Digidirect\FreeGift\Plugin\Magento\OfflineShipping\Model\SalesRule;

use Magento\OfflineShipping\Model\SalesRule\Calculator as OriginalCalculator;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class Calculator
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
     * @param OriginalCalculator $subject
     * @param \Closure $proceed
     * @param AbstractItem $item
     * @return \Magento\OfflineShipping\Model\SalesRule\Calculator
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcessFreeShipping(OriginalCalculator $subject, \Closure $proceed, AbstractItem $item)
    {
        $result = $proceed($item);
        if ($this->_dataHelper->isHiddenForCustomerGiftItem($item)) {
            $item->setFreeShipping($item->getQty());
        }
        return $result;
    }
}
