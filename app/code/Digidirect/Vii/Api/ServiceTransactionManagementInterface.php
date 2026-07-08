<?php

namespace Digidirect\Vii\Api;

interface ServiceTransactionManagementInterface
{
    /**
     * @param int $quoteId
     * @param null|\Magento\Sales\Api\Data\OrderInterface $order
     * @param bool $forceRemove
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reversePreviousTransaction($quoteId, $order = null, $forceRemove = false);

    /**
     * @param \Digidirect\Vii\Model\Service\Adapter $service
     * @return bool
     */
    public function pushUndoProcessToQueue($service);
}
