<?php

namespace Digidirect\ExtendedCartPriceRules\Plugin\Magento\Wishlist\Model;

/**
 * Class Item
 * @package Digidirect\ExtendedCartPriceRules\Plugin\Magento\Wishlist\Model
 */
class Item
{
    /**
     * @var \Magento\Wishlist\Model\Item
     */
    protected $wishlistItemToAdd;

    /**
     * @param \Magento\Wishlist\Model\Item $subject
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete
     * @return array
     */
    public function beforeAddToCart(
        \Magento\Wishlist\Model\Item $subject,
        \Magento\Checkout\Model\Cart $cart,
        $delete = false
    ) {
        $this->wishlistItemToAdd = $subject;
        if ($delete) {
            $delete = false;
        }
        return [$cart, $delete];
    }

    /**
     * @return mixed
     */
    public function getWishlistItemToAdd()
    {
        return $this->wishlistItemToAdd;
    }
}
