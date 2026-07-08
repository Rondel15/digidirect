<?php

namespace Digidirect\AI\Model\Lib\Entity\Export\Sales\Order\Stuck\Api;

use Magento\Sales\Api\Data\OrderInterface;

interface StuckOrderRepositoryInterface
{
    /**
     * @param string|null $processCode
     * @param string|null $productSku
     * @return mixed
     */
    public function getSum($processCode = null, $productSku = null);

    /**
     * @param OrderInterface $order
     * @param string|null $processCode
     * @return mixed
     */
    public function addOrder(OrderInterface $order, $processCode = null);

    /**
     * @param int $orderEntityId
     * @param string|null $processCode
     * @return mixed
     */
    public function addOrderById($orderEntityId, $processCode = null);

    /**
     * @param string $incrementId
     * @param string|null $processCode
     * @return mixed
     */
    public function addOrderByIncrementId($incrementId, $processCode = null);

    /**
     * @param int $orderId
     * @return mixed
     */
    public function deleteOrderById($orderId);

    /**
     * @param string $incrementId
     * @return mixed
     */
    public function deleteOrderByIncrementId($incrementId);

    /**
     * @param OrderInterface $order
     * @return mixed
     */
    public function deleteOrder(OrderInterface $order);
}
