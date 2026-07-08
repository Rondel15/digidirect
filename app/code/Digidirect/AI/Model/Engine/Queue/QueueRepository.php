<?php

namespace Digidirect\AI\Model\Engine\Queue;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsFactory;
use Digidirect\AI\Api\QueueRepositoryInterface;
use Digidirect\AI\Api\Data\QueueInterface;
use Digidirect\AI\Model\ResourceModel\Queue\Queue as QueueResource;
use Digidirect\AI\Model\ResourceModel\Queue\Queue\CollectionFactory as QueueCollectionFactory;
use Digidirect\AI\Model\ResourceModel\Queue\Queue\Collection as QueueCollection;
use Digidirect\AI\Model\Engine\Queue\State as QueueState;

/**
 * Class QueueRepository
 *
 * @package Digidirect\AI\Model\Engine\Queue
 */
class QueueRepository implements QueueRepositoryInterface
{
    /**
     * Resource
     *
     * @var QueueResource
     */
    protected $resource;

    /**
     * Search Results Factory
     *
     * @var SearchResultsFactory
     */
    protected $searchResultsFactory;

    /**
     * Queues By Id
     *
     * @var QueueInterface[]
     */
    protected $queuesById = [];

    /**
     * Queue Factory
     *
     * @var QueueFactory
     */
    protected $queueFactory;

    /**
     * Queue Collection
     *
     * @var QueueCollectionFactory
     */
    protected $queueCollectionFactory;

    /**
     * QueueRepository constructor.
     *
     * @param QueueResource $resource
     * @param SearchResultsFactory $searchResultsFactory
     * @param QueueFactory $queueFactory
     * @param QueueCollectionFactory $queueCollectionFactory
     */
    public function __construct(
        QueueResource $resource,
        SearchResultsFactory $searchResultsFactory,
        QueueFactory $queueFactory,
        QueueCollectionFactory $queueCollectionFactory
    ) {
        $this->resource = $resource;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->queueFactory = $queueFactory;
        $this->queueCollectionFactory = $queueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function storeRelatedLogId($queueId, $logId)
    {
        $connection = $this->resource->getConnection();
        $connection->insertOnDuplicate(
            $this->resource->getTable('digidirect_ai_queue_log'),
            ['queue_id' => $queueId, 'log_id' => $logId]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function get($queueId, $reload = false)
    {
        if (!isset($this->queuesById[$queueId]) || $reload) {
            /** @var \Digidirect\AI\Model\Engine\Queue\Queue $queue */
            $queue = $this->queueFactory->create()->load($queueId);
            if (!$queue->getId()) {
                // queue does not exist
                throw NoSuchEntityException::singleField('queueId', $queueId);
            }
            $this->queuesById[$queueId] = $queue;
        }

        return $this->queuesById[$queueId];
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveQueueItemIdByProcessCodeAndProcessData($processCode, $processData)
    {
        return $this->resource->getActiveQueueItemIdByProcessCodeAndProcessData($processCode, $processData);
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $queue)
    {
        /**
         * @var $existingQueueItem QueueInterface|\Digidirect\AI\Model\Engine\Queue\Queue
         * @var $queue QueueInterface|\Digidirect\AI\Model\Engine\Queue\Queue
         * @var $collection QueueCollection
         */
        if ($queue->getId()) {
            $queue = $this->get($queue->getId())->addData($queue->getData());
        } else {
            //maybe queue item with the same params exists
            $processData = $queue->getProcessData();
            $processDataCondition = $processData === null ? ['null' => true] : $processData;
            $collection = $this->queueCollectionFactory->create();
            $collection->addFieldToFilter(QueueInterface::PROCESS_CODE, $queue->getProcessCode());
            $collection->addFieldToFilter(QueueInterface::PROCESS_DATA, $processDataCondition);
            $collection->addFieldToFilter(QueueInterface::STATE, ['neq' => QueueState::STATE_CLOSED]);
            $collection->setPageSize(1);
            if (count($collection)) {
                $existingQueueItem = $collection->getFirstItem();
                $queue->setData($existingQueueItem->getData());
                $queue->setOrigData();
                $queue->setSequence(0);
                $queue->setRunNumber(0);
            }
        }

        try {
            $this->resource->save($queue);
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(__('Could not save the queue: %1', $exception->getMessage()));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $queue)
    {
        throw new CouldNotDeleteException(__('Could not delete the queue: Please mark as retired.'));
    }

    /**
     * {@inheritdoc}
     */
    public function retire(QueueInterface $queue)
    {
        $queue->setIsRetired(true);
        $this->save($queue);
    }

    /**
     * {@inheritdoc}
     */
    public function retireById($id)
    {
        $this->retire($this->get($id));
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var QueueCollection $collection */
        $collection = $this->queueCollectionFactory->create();
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $filtersGroup = [];
            $filtersConditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $filtersGroup[] = [$filter->getField()];
                $filtersConditions[] = [$condition => $filter->getValue()];
            }
            $collection->addFieldToFilter($filtersGroup, $filtersConditions);
        }

        /** @var SortOrder $sortOrder */
        foreach ((array)$searchCriteria->getSortOrders() as $sortOrder) {
            $field = $sortOrder->getField();
            $collection->addOrder($field, $sortOrder->getDirection());
        }

        $collection->setCurPage($searchCriteria->getCurrentPage());
        $collection->setPageSize($searchCriteria->getPageSize());
        $collection->load();

        return $this->searchResultsFactory->create()
            ->setItems($collection->getItems())
            ->setTotalCount($collection->getSize())
            ->setSearchCriteria($searchCriteria);
    }

    /**
     * Get Next SequenceId
     *
     * @return int
     */
    public function getNextSequenceId()
    {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getMainTable(),
            ['sequence' => new \Zend_Db_Expr('MAX(' . QueueInterface::SEQUENCE . ')')]
        );

        return intval($connection->fetchOne($select)) + 1;
    }
}
