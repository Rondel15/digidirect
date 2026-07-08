<?php

namespace Digidirect\Digi\Rewrite\Wishlist\CustomerData;

/**
 * Class Wishlist
 * @package Digidirect\Digi\Rewrite\CustomData
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 4;
    /**
     * Get wishlist items
     *
     * @return array
     */
    protected function getItems()
    {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize(self::SIDEBAR_ITEMS_NUMBER)
            ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }
}
