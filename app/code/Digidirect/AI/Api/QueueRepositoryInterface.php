<?php
namespace Digidirect\AI\Api;

use \Magento\Framework\Api\SearchCriteriaInterface;
use \Magento\Framework\Api\SearchResultsInterface;
use \Magento\Framework\Exception\NoSuchEntityException;
use \Digidirect\AI\Api\Data\QueueInterface;

/**
 * Interface QueueRepositoryInterface
 *
 * @package Digidirect\AI\Api
 */
interface QueueRepositoryInterface
{
    /**
     * Retrieve specified queue.
     *
     * @param int $queueId
     * @return QueueInterface
     * @throws NoSuchEntityException
     */
    public function get($queueId);

    /**
     * Save queue
     *
     * @param QueueInterface $queue
     * @return void
     */
    public function save(QueueInterface $queue);

    /**
     * Delete queue
     *
     * @param QueueInterface $queue
     * @throws NoSuchEntityException
     * @return void
     */
    public function delete(QueueInterface $queue);

    /**
     * Retire queue
     *
     * @param QueueInterface $queue
     * @throws NoSuchEntityException
     * @return void
     */
    public function retire(QueueInterface $queue);

    /**
     * Retire queue by if
     *
     * @param int $id
     * @return bool
     * @throws NoSuchEntityException If a ID is sent but the entity does not exist
     */
    public function retireById($id);
    
    /**
     * Get list queues that match specified search criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
    
    /**
     * Retrieve next sequence Id
     *
     * @return int
     */
    public function getNextSequenceId();

    /**
     * Store Related Log Id
     *
     * @param int $queueId
     * @param int $logId
     * @return void
     */
    public function storeRelatedLogId($queueId, $logId);

    /**
     * @param string $processCode
     * @param string|null $processData
     * @return int|null
     */
    public function getActiveQueueItemIdByProcessCodeAndProcessData($processCode, $processData);
}
