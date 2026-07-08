<?php
namespace Digidirect\FreeGift\Plugin\ConfigurableProduct\Model\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;

class CartItemProcessor
{
    /**
     * @param \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor $subject
     * @param \Closure $proceed
     * @param CartItemInterface $cartItem
     * @return CartItemInterface
     */
    public function aroundProcessOptions(
        \Magento\ConfigurableProduct\Model\Quote\Item\CartItemProcessor $subject,
        \Closure $proceed,
        CartItemInterface $cartItem
    ) {
        $attributesOption = $cartItem->getProduct()->getCustomOption('attributes');
        if ($attributesOption !== null) {
            return $proceed($cartItem);
        }
        return $cartItem;
    }
}
