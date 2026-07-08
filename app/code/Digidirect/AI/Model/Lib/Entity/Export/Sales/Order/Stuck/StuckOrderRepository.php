<?php

namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck;

use Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\Api\StuckOrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;

class StuckOrderRepository implements StuckOrderRepositoryInterface
{
    /**
     * @var StuckOrderDb
     */
    protected $stuckOrderDb;

    /**
     * StuckOrderRepository constructor.
     *
     * @param StuckOrderDb $stuckOrderDb
     */
    public function __construct(StuckOrderDb $stuckOrderDb)
    {
        $this->stuckOrderDb = $stuckOrderDb;
    }

    /**
     * @param OrderInterface $order
     * @param null $processCode
     * @return void
     */
    public function addOrder(OrderInterface $order, $processCode = null)
    {
        try {
            $items = $order->getItems();
            $itemsToSave = [];
            foreach ($items as $item) {
                $itemsToSave[$item->getSku()] =
                    $item->getQtyOrdered() - $item->getQtyCanceled() - $item->getQtyRefunded() - $item->getQtyShipped();
            }
            $this->stuckOrderDb->addOrder($order->getEntityId(), $processCode, $itemsToSave);
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * @param int $orderEntityId
     * @param null $processCode
     * @return void
     */
    public function addOrderById($orderEntityId, $processCode = null)
    {
        try {
            $this->stuckOrderDb->addOrder($orderEntityId, $processCode);
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * @param string $orderIncrementId
     * @param null $processCode
     * @return void
     */
    public function addOrderByIncrementId($orderIncrementId, $processCode = null)
    {
        try {
            $this->stuckOrderDb->addOrder(
                $this->stuckOrderDb->getOrderIdByIncrementId($orderIncrementId),
                $processCode
            );
        } catch (\Throwable $throwable) {
            return;
        }
    }

    /**
     * @param null $processCode
     * @param null $productSku
     * @return array|int
     */
    public function getSum($processCode = null, $productSku = null)
    {
        $result = $this->stuckOrderDb->getSum($processCode, $productSku);
        if ($productSku) {
            return $result[$productSku] ?? 0;
        }

        return $result;
    }

    /**
     * @param int $orderId
     * @return void
     */
    public function deleteOrderById($orderId)
    {
        try {
            $this->stuckOrderDb->deleteByOrderId($orderId);
        } catch (\Throwable $exception) {
            return;
        }
    }

    /**
     * @param string $incrementId
     * @return void
     */
    public function deleteOrderByIncrementId($incrementId)
    {
        try {
            $this->stuckOrderDb->deleteByOrderId($this->stuckOrderDb->getOrderIdByIncrementId($incrementId));
        } catch (\Throwable $exception) {
            return;
        }
    }

    /**
     * @param OrderInterface $order
     * @return void
     */
    public function deleteOrder(OrderInterface $order)
    {
        $this->deleteOrderById($order->getEntityId());
    }
}
