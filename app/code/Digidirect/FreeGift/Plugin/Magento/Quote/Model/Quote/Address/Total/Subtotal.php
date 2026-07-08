<?php

namespace Digidirect\FreeGift\Plugin\Magento\Quote\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address\Total\Subtotal as OriginalSubtotal;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Item as AddressItem;
use Digidirect\FreeGift\Helper\Data as DataHelper;

class Subtotal
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
     * @param OriginalSubtotal $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param Address\Total $total
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCollect(
        OriginalSubtotal $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        /**
         * @var \Magento\Quote\Model\Quote\Item $quoteItem
         */
        $items = $prevItems = $shippingAssignment->getItems();
        foreach ($items as $k => $item) {
            if ($item instanceof AddressItem) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            } else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem()) {
                if ($this->_dataHelper->isHiddenForCustomerGiftItem($quoteItem)) {
                    unset($items[$k]);
                }
            }
        }

        $shippingAssignment->setItems(array_values($items));
        $data = $proceed($quote, $shippingAssignment, $total);
        $shippingAssignment->setItems($prevItems);

        return $data;
    }
}
