<?php

namespace Digidirect\AI\Helper;

use Magento\Framework\App\Helper\Context;
use Digidirect\AI\Model\Engine\Processor\ProcessorAbstract;
use Digidirect\AI\Model\Engine\Queue\QueueFactory;
use Digidirect\AI\Model\Engine\Queue\QueueRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Digidirect\AI\Api\Data\QueueInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;

/**
 * Class Queue
 *
 * @package Digidirect\AI\Helper
 */
class Queue extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Queue Factory
     *
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * Queue Factory
     *
     * @var QueueRepository
     */
    protected $queueRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * Queue constructor.
     *
     * @param Context $context
     * @param QueueFactory $queueFactory
     * @param QueueRepository $queueRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context $context,
        QueueFactory $queueFactory,
        QueueRepository $queueRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param string $processCode
     * @param string $processData
     * @param string $initiator
     * @return QueueInterface
     */
    public function createQueueItem($processCode, $processData, $initiator)
    {
        /**
         * @var $queue QueueInterface
         */
        $queue = $this->queueFactory->create();
        $queue->setInitiator($initiator);
        $queue->setProcessData($processData);
        $queue->setProcessCode($processCode);
        $queue->setSequence(0);
        $queue->setRunNumber(0);
        $this->queueRepository->save($queue);

        return $queue;
    }

    /**
     * Put processor to queue
     *
     * @param string $initiator
     * @param ProcessorAbstract $processor
     * @return $this
     */
    public function put($initiator, ProcessorAbstract $processor)
    {
        /**
         * @var $queue QueueInterface
         */
        $processData = $processor->convertRunOptionsToQueueData();
        try {
            if (!$processor->getQueueId()) {
                throw new NoSuchEntityException(__('QueueId is empty'));
            }
            $queue = $this->queueRepository->get($processor->getQueueId());
            if ($processData != $queue->getProcessData()) {
                $queue->setProcessData($processData);
                $this->queueRepository->save($queue);
            }
        } catch (NoSuchEntityException $e) {
            //if queue doe not exists for the process then need add
            $queue = $this->createQueueItem($processor->getProcessCode(), $processData, $initiator);
            $this->saveQueueLogRelation($queue->getId(), $processor->getLogger()->getLogRecordIdentifier());
        }

        return $this;
    }

    /**
     * @param int $queueId
     * @param int $logId
     * @return $this
     */
    public function saveQueueLogRelation($queueId, $logId)
    {
        $this->queueRepository->storeRelatedLogId($queueId, $logId);
        return $this;
    }

    /**
     * @param array $data
     * @return null|string
     */
    public static function getSerializedProcessData($data)
    {
        if (empty($data)) {
            return null;
        }
        return serialize($data);
    }

    /**
     * @param string $data
     * @return array
     */
    public static function getUnserializedProcessData($data)
    {
        if (!$data) {
            return [];
        }
        return unserialize($data);
    }
}
