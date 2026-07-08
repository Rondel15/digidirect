<?php

namespace Digidirect\ExtendedCartPriceRules\Model\ResourceModel\Sales;

use Magento\Sales\Api\Data\OrderInterface;

class Order extends \Magento\Sales\Model\ResourceModel\Order
{
    /**
     * @param null|int $customerId
     * @param string $customerEmail
     * @param array $orderStatuses
     * @return int
     */
    public function getCustomerOrdersCount($customerId, $customerEmail, array $orderStatuses)
    {
        if (!$customerId && !$customerEmail) {
            return 0;
        }

        $connection = $this->getConnection();

        $customerCondition = $connection->quoteInto(OrderInterface::CUSTOMER_EMAIL . ' = ?', $customerEmail);
        if ($customerId) {
            $customerCondition .= ' OR ' . $connection->quoteInto(OrderInterface::CUSTOMER_ID . ' = ?', $customerId);
        }

        $select = $connection->select();
        $select->from($this->getMainTable(), new \Zend_Db_Expr('COUNT(*)'))
            ->where($customerCondition)
            ->where(OrderInterface::STATUS . ' IN (?)', $orderStatuses);

        return (int)$connection->fetchOne($select);
    }

    /**
     * @param null|int $customerId
     * @param string $customerEmail
     * @return string|null
     */
    public function getLastCustomersOrderStatus($customerId, $customerEmail)
    {
        if (!$customerId && !$customerEmail) {
            return null;
        }

        $connection = $this->getConnection();

        $customerCondition = $connection->quoteInto(OrderInterface::CUSTOMER_EMAIL . ' = ?', $customerEmail);
        if ($customerId) {
            $customerCondition .= ' OR ' . $connection->quoteInto(OrderInterface::CUSTOMER_ID . ' = ?', $customerId);
        }

        $select = $connection->select();
        $select->from($this->getMainTable(), OrderInterface::STATUS)
            ->where($customerCondition)
            ->limit(1)
            ->order(OrderInterface::CREATED_AT . ' ' . \Zend_Db_Select::SQL_DESC);

        return $connection->fetchOne($select);
    }

    /**
     * @param int $customerId
     * @param array $skuArray
     * @return string|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLastBoughtProductDateByCustomerIdAndSkus($customerId, array $skuArray)
    {
        if (empty($customerId) || empty($skuArray)) {
            return false;
        }

        $adapter = $this->getConnection();
        $select = $adapter->select();
        $select->from(['order' => $this->getMainTable()], [])
            ->join(
                ['order_item' => $this->getTable('sales_order_item')],
                'order.entity_id = order_item.order_id',
                [
                    'created_at' => new \Zend_Db_Expr('MAX(order_item.created_at)')
                ]
            )
            ->where('order.customer_id = ?', $customerId)
            ->where('order_item.sku IN (?)', $skuArray)
            ->where('order_item.qty_invoiced > ?', 0)
            ->where('order_item.qty_invoiced > order_item.qty_refunded');

        $result = $adapter->fetchOne($select);
        return $result ?: false;
    }
}
