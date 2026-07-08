<?php

namespace Digidirect\AI\Model\ResourceModel\Queue;

use Digidirect\AI\Api\Data\QueueInterface;

/**
 * Class Queue
 *
 * @package Digidirect\AI\Model\ResourceModel\Queue
 */
class Queue extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('digidirect_ai_queue', 'id');
    }

    /**
     * @param string $processCode
     * @param string|null $processData
     * @return int|false
     */
    public function getActiveQueueItemIdByProcessCodeAndProcessData($processCode, $processData)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select();
        $select->from($this->getMainTable(), $this->getIdFieldName())
            ->where(QueueInterface::PROCESS_CODE . ' = ?', $processCode)
            ->where(QueueInterface::STATE . ' <> ?', \Digidirect\AI\Model\Engine\Queue\State::STATE_CLOSED);

        if ($processData === null) {
            $select->where(QueueInterface::PROCESS_DATA . ' IS NULL');
        } else {
            $select->where(QueueInterface::PROCESS_DATA . ' = ?', $processData);
        }

        return $adapter->fetchOne($select);
    }
}
