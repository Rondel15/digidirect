<?php
namespace Digidirect\Collect\Model\Plugin\Quote;

/**
 * Class Item
 *
 * @package Digidirect\Collect\Model\Plugin\Quote
 */
class Item
{
    /**
     * Set Product As Virtual for CC
     *
     * @param \Magento\Quote\Model\Quote\Item $subject
     * @param \Closure $proceed
     * @return \Magento\Catalog\Model\Product
     */
    public function aroundGetProduct(
        \Magento\Quote\Model\Quote\Item $subject,
        \Closure $proceed
    ) {
        $product = $proceed();

        if ($subject->getCollectPlaceId()
            || ($subject->getParentItem() && $subject->getParentItem()->getCollectPlaceId())) {
            $product->setIsCollectDelivery(true);
        } else {
            $product->setIsCollectDelivery(false);
        }

        return $product;
    }
}
