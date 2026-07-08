<?php
namespace Digidirect\AI\Model\Integrations;

use Digidirect\AI\Api\Data\ScheduleSearchResultInterfaceFactory;
use Digidirect\AI\Model\ResourceModel\Integrations\Schedule;
use Digidirect\AI\Model\ResourceModel\Integrations\Schedule\CollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class ScheduleRepository
 *
 * @package Digidirect\AI\Model\Integrations
 */
class ScheduleRepository implements \Digidirect\AI\Api\ScheduleRepositoryInterface
{
    /**
     * @var Schedule
     */
    protected $resource;

    /**
     * @var ScheduleFactory
     */
    protected $scheduleFactory;

    /**
     * @var \Digidirect\AI\Model\ResourceModel\Integrations\Schedule\Collection
     */
    protected $scheduleCollectionFactory;

    /**
     * @var ScheduleSearchResultInterfaceFactory
     */
    protected $searchResultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * ScheduleRepository constructor.
     *
     * @param Schedule $resource
     * @param ScheduleFactory $scheduleFactory
     * @param CollectionFactory $scheduleCollectionFactory
     * @param ScheduleSearchResultInterfaceFactory $searchResultFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        Schedule $resource,
        ScheduleFactory $scheduleFactory,
        CollectionFactory $scheduleCollectionFactory,
        ScheduleSearchResultInterfaceFactory $searchResultFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {

        $this->resource = $resource;
        $this->scheduleFactory = $scheduleFactory;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Digidirect\AI\Api\Data\ScheduleInterface $schedule)
    {
        try {
            $this->resource->save($schedule);
        } catch (\Throwable $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Schedule: %1',
                $exception->getMessage()
            ));
        }
        return $schedule;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id)
    {
        $schedule = $this->scheduleFactory->create();
        $this->resource->load($schedule, $id);
        if (!$schedule->getId()) {
            throw new NoSuchEntityException(__('Schedule with id "%1" does not exist.', $id));
        }
        return $schedule;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var \Digidirect\AI\Api\Data\ScheduleSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();
        $this->collectionProcessor->process($criteria, $searchResult);
        $searchResult->setSearchCriteria($criteria);
        return $searchResult;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Digidirect\AI\Api\Data\ScheduleInterface $schedule)
    {
        try {
            $this->resource->delete($schedule);
        } catch (\Throwable $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Schedule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($eventId)
    {
        return $this->delete($this->getById($eventId));
    }
}
