<?php

namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Config\CacheInterface;

class StuckOrderDb extends AbstractDb
{
    const AI_STUCK_ORDERS = 'digidirect_ai_stuck_orders';
    const ENTITY_ID = 'entity_id';
    const ORDER_ENTITY_ID = 'order_entity_id';
    const SKU = 'sku';
    const QTY = 'qty';
    const PROCESS_CODE = 'process_code';

    const MAIN_CACHE_TAG = 'stuck_tag_collection';

    /**
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * StuckOrderDb constructor.
     *
     * @param Context $context
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        CacheInterface $cache,
        SerializerInterface $serializer,
        $connectionName = null
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(static::AI_STUCK_ORDERS, 'entity_id');
    }

    /**
     * @param string $orderIncrementId
     * @return string
     */
    public function getOrderIdByIncrementId($orderIncrementId)
    {
        $select = $this->getConnection()->select()
            ->from([$this->getOrderTableAlias() => $this->getOrderTable()], OrderInterface::ENTITY_ID)
            ->where($this->getConnection()->quoteInto(OrderInterface::INCREMENT_ID . ' = ?', $orderIncrementId));
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * @return string
     */
    protected function getOrderTable()
    {
        return $this->getTable('sales_order');
    }

    /**
     * @return string
     */
    protected function getOrderItemTable()
    {
        return $this->getTable('sales_order_item');
    }

    /**
     * @return string
     */
    protected function getOrderItemTableAlias()
    {
        return 'oi';
    }

    /**
     * @return string
     */
    protected function getOrderTableAlias()
    {
        return 'o';
    }

    /**
     * @param int $orderId
     * @param null $integrationProcessCode
     * @param array $items
     * @return void
     */
    public function addOrder($orderId, $integrationProcessCode = null, array $items = [])
    {
        if (empty($items)) {
            $items = $this->getOrderItems($orderId);
        }

        $insertObDuplicatePrepared = [];
        foreach ($items as $sku => $qty) {
            $insertObDuplicatePrepared[] = [
                self::SKU => $sku,
                self::QTY => $qty,
                self::ORDER_ENTITY_ID => $orderId,
                self::PROCESS_CODE => $integrationProcessCode,
            ];
        }

        $this->getConnection()->insertOnDuplicate($this->getMainTable(), $insertObDuplicatePrepared, [self::QTY]);
    }

    /**
     * @param int $orderId
     * @return array
     */
    protected function getOrderItems($orderId)
    {
        $select = $this->getConnection()->select()
            ->from(
                [$this->getOrderItemTableAlias() => $this->getOrderItemTable()],
                [
                    OrderItemInterface::SKU,
                    'qty' => new \Zend_Db_Expr(
                        '(' .
                        $this->getOrderItemTableAlias() . '.' . OrderItemInterface::QTY_ORDERED . ' - ' .
                        $this->getOrderItemTableAlias() . '.' . OrderItemInterface::QTY_SHIPPED . ' - ' .
                        $this->getOrderItemTableAlias() . '.' . OrderItemInterface::QTY_REFUNDED . ' - ' .
                        $this->getOrderItemTableAlias() . '.' . OrderItemInterface::QTY_CANCELED . ')'
                    ),
                ]
            )
            ->where(
                $this->getQuoteIntoExpression(
                    $this->getOrderItemTableAlias() . '.' . OrderItemInterface::ORDER_ID . ' = ?',
                    $orderId
                )
            );

        return $this->getConnection()->fetchPairs($select);
    }

    /**
     * @param string $text
     * @param string|mixed $value
     * @param null $type
     * @param null $count
     * @return string
     */
    protected function getQuoteIntoExpression($text, $value, $type = null, $count = null)
    {
        return $this->getConnection()->quoteInto($text, $value, $type, $count);
    }

    /**
     * @param null $processCode
     * @param null $productSku
     * @return array
     */
    public function getSum($processCode = null, $productSku = null)
    {
        $select = $this->getConnection()
            ->select()
            ->from(
                $this->getMainTable(),
                [
                    self::SKU,
                    self::QTY => new \Zend_Db_Expr('SUM(qty)'),
                ]
            )->group(self::SKU);
        if ($processCode) {
            $select->where($this->getQuoteIntoExpression(self::PROCESS_CODE . ' = ?', $processCode));
        }

        if ($productSku) {
            if (!is_array($productSku)) {
                $productSku = [$productSku];
            }
            $select->where($this->getQuoteIntoExpression(self::SKU . ' IN (?)', $productSku));
        }

        $result = $this->getConnection()->fetchPairs($select);

        return $result;
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function deleteByOrderId($orderId)
    {
        $this->getConnection()->delete(
            $this->getMainTable(),
            $this->getQuoteIntoExpression(self::ORDER_ENTITY_ID . ' = ?', $orderId)
        );
    }
}
