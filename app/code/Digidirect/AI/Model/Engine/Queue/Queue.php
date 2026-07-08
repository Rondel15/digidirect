<?php
namespace Digidirect\AI\Model\Engine\Queue;

use Magento\Framework\Model\AbstractModel;
use Digidirect\AI\Api\Data\QueueInterface;

/**
 * Class Queue
 *
 * @package Digidirect\AI\Model\Engine\Queue
 */
class Queue extends AbstractModel implements QueueInterface
{
    /**
     * Init method
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\AI\Model\ResourceModel\Queue\Queue');
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessCode()
    {
        return $this->getData(QueueInterface::PROCESS_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function getInitiator()
    {
        return $this->getData(QueueInterface::INITIATOR);
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessData()
    {
        return $this->getData(QueueInterface::PROCESS_DATA);
    }

    /**
     * {@inheritdoc}
     */
    public function getState()
    {
        return $this->getData(QueueInterface::STATE);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(QueueInterface::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function getIsRetired()
    {
        return $this->getData(QueueInterface::IS_RETIRED);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSequence()
    {
        return $this->getData(QueueInterface::SEQUENCE);
    }

    /**
     * {@inheritdoc}
     */
    public function getRunNumber()
    {
        return $this->getData(QueueInterface::RUN_NUMBER);
    }

    /**
     * {@inheritdoc}
     */
    public function getLastRunAt()
    {
        return $this->getData(QueueInterface::LAST_RUN_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(QueueInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessCode($processCode)
    {
        return $this->setData(QueueInterface::PROCESS_CODE, $processCode);
    }

    /**
     * {@inheritdoc}
     */
    public function setInitiator($initiator)
    {
        return $this->setData(QueueInterface::INITIATOR, $initiator);
    }

    /**
     * {@inheritdoc}
     */
    public function setProcessData($processData)
    {
        return $this->setData(QueueInterface::PROCESS_DATA, $processData);
    }

    /**
     * {@inheritdoc}
     */
    public function setState($state)
    {
        return $this->setData(QueueInterface::STATE, $state);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(QueueInterface::STATUS, $status);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsRetired($isRetired)
    {
        return $this->setData(QueueInterface::IS_RETIRED, $isRetired);
    }
    
    /**
     * {@inheritdoc}
     */
    public function setSequence($sequence)
    {
        return $this->setData(QueueInterface::SEQUENCE, $sequence);
    }

    /**
     * {@inheritdoc}
     */
    public function setRunNumber($runNumber)
    {
        return $this->setData(QueueInterface::RUN_NUMBER, $runNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function setLastRunAt($lastRunAt)
    {
        return $this->setData(QueueInterface::LAST_RUN_AT, $lastRunAt);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(QueueInterface::CREATED_AT, $createdAt);
    }
}
