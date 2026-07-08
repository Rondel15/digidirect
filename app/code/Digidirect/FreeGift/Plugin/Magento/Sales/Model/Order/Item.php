<?php

namespace Digidirect\FreeGift\Plugin\Magento\Sales\Model\Order;

use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Framework\Registry;

class Item
{
    const FIX_FREE_GIFT_GIFTCARD_AMOUNT_FLAG = '_fix_free_gift_giftcard_amount_flag';

    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * Item constructor.
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->_registry = $registry;
    }

    /**
     * method is used to hide hidden free gifts
     *
     * @param OrderItem $orderItem
     * @param null|OrderItem $result
     * @return null|OrderItem
     */
    public function afterGetParentItem(OrderItem $orderItem, $result)
    {
        if (!$result) {
            if ($this->_registry->registry(\Digidirect\FreeGift\Helper\Data::REGISTRY_HIDE_FREE_GIFT_ORDER_ITEMS)) {
                $optionData = $orderItem->getProductOptionByCode('info_buyRequest');
                if (isset($optionData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY])) {
                    $result = $orderItem;
                }
            }
            if ($this->_registry->registry(\Digidirect\FreeGift\Helper\Data::REGISTRY_HIDE_HIDDEN_FREE_GIFT_ORDER_ITEMS)) {
                $optionData = $orderItem->getProductOptionByCode('info_buyRequest');
                if (!empty($optionData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_IS_HIDDEN_FOR_CUSTOMER])) {
                    $result = $orderItem;
                }
            }
        }
        return $result;
    }

    /**
     * method is used to fix giftcard amount
     *
     * @param OrderItem $orderItem
     * @param float|null $result
     * @return float
     */
    public function afterGetBasePrice(OrderItem $orderItem, $result)
    {
        if (!$this->_registry->registry(self::FIX_FREE_GIFT_GIFTCARD_AMOUNT_FLAG)) {
            return $result;
        }

        if ($orderItem->getProductType() != 'giftcard') {
            return $result;
        }

        $optionData = $orderItem->getProductOptionByCode('info_buyRequest');
        if (!isset($optionData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY])
            || $optionData['options'][\Digidirect\FreeGift\Model\Cart\Item::FREE_GIFT_KEY] !== false
        ) {
            return $result;
        }

        return $optionData['giftcard_amount'];
    }
}
