<?php

namespace Digidirect\ExtendedCartPriceRules\Helper;

use Magento\Framework\App\Helper\AbstractHelper as AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Config extends AbstractHelper
{
    const ORDER_STATUSES_FOR_ORDERS_COUNT = 'digidirect_extendedcartpricerules/general/order_statuses_for_orders_count';

    /**
     * @param mixed $store
     * @return array
     */
    public function getOrderStatusesForOrdersCount($store = null)
    {
        $orderStatuses = $this->scopeConfig->getValue(
            self::ORDER_STATUSES_FOR_ORDERS_COUNT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        return $orderStatuses ? explode(',', $orderStatuses) : [];
    }
}
