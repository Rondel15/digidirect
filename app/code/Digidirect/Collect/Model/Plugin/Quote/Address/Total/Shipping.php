<?php

namespace Digidirect\Collect\Model\Plugin\Quote\Address\Total;

class Shipping
{

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total\Shipping $subject
     * @param \Closure $proceed
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return mixed
     */
    public function aroundCollect(
        \Magento\Quote\Model\Quote\Address\Total\Shipping $subject,
        \Closure $proceed,
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $itemsBase = $shippingAssignment->getItems();
        $items = $shippingAssignment->getItems();

        if (!empty($items)) {
            foreach ($items as $key => $item) {
                /** @var $item \Magento\Quote\Model\Quote\Item*/
                if ($item->getCollectPlaceId() && $item->getChildren()) {
                    unset($items[$key]);
                }
            }
            $shippingAssignment->setItems($items);
            $result = $proceed($quote, $shippingAssignment, $total);
            $shippingAssignment->setItems($itemsBase);

            return $result;
        }

        return $proceed($quote, $shippingAssignment, $total);
    }
}
