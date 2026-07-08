<?php

namespace Digidirect\ExtendedCartPriceRules\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Digidirect\ExtendedCartPriceRules\Helper\Data as DataHelper;

class SalesQuoteAddressCollectTotalsBefore implements ObserverInterface
{
    /**
     * @var DataHelper
     */
    protected $dataHelper;

    /**
     * SalesQuoteAddressCollectTotalsBefore constructor.
     * @param DataHelper $dataHelper
     */
    public function __construct(DataHelper $dataHelper)
    {
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /**
         * @var \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
         * @var \Magento\Quote\Model\Quote\Item\AbstractItem $item
         */
        $shippingAssignment = $observer->getEvent()->getData('shipping_assignment');
        $items = $shippingAssignment->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            $hasActiveSpecialPrice = (int)$this->dataHelper->hasQuoteItemActiveSpecialPrice($item);
            $product->setData(DataHelper::HAS_ACTIVE_SPECIAL_PRICE_ATTR_CODE, $hasActiveSpecialPrice);
        }
    }
}
