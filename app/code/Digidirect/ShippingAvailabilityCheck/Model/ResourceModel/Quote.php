<?php

namespace Digidirect\ShippingAvailabilityCheck\Model\ResourceModel;

use Digidirect\ShippingAvailabilityCheck\Api\Data\Quote\CartInterface;

/**
 * Class Quote
 * @package Digidirect\ShippingAvailabilityCheck\Model\ResourceModel
 */
class Quote extends \Magento\Quote\Model\ResourceModel\Quote
{
    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param string $hash
     * @param int|null $customerId
     * @return $this
     */
    public function loadByShippingAvailabilityCheckHash($quote, $hash, $customerId = null)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())
            ->where('is_active = ?', 0)
            ->where(CartInterface::SHIPPING_AVAILABILITY_CHECK_HASH . ' like ?', $hash)
            ->limit(1);

        if ($customerId) {
            $select->where('customer_id = ?', $customerId);
        } else {
            $select->where('customer_id is Null');
        }

        $storeIds = $quote->getSharedStoreIds();
        if ($storeIds) {
            if ($storeIds != ['*']) {
                $select->where('store_id IN (?)', $storeIds);
            }
        } else {
            /**
             * For empty result
             */
            $select->where('store_id < ?', 0);
        }

        $data = $connection->fetchRow($select);

        if ($data) {
            $quote->setData($data);
        }

        $this->_afterLoad($quote);

        return $this;
    }
}
