<?php

namespace Digidirect\Feed\Model;

use Digidirect\Feed\Api\FeedRepositoryInterface;
use Digidirect\Feed\Api\Data;
use Digidirect\Feed\Api\Data\FeedInterface;
use Digidirect\Feed\Model\ResourceModel\Feed as ResourceFeed;
use Digidirect\Feed\Model\ResourceModel\Feed\CollectionFactory as FeedCollectionFactory;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class FeedRepository implements FeedRepositoryInterface
{
    /**
     * @var ResourceFeed
     */
    protected $resource;

    /**
     * @var FeedFactory
     */
    protected $feedFactory;

    /**
     * @var Data\FeedInterfaceFactory
     */
    protected $dataFeedFactory;

    /**
     * @var FeedCollectionFactory
     */
    protected $feedCollectionFactory;

    /**
     * @var Data\FeedSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * FeedRepository constructor.
     * @param ResourceFeed $resource
     * @param FeedFactory $feedFactory
     * @param Data\FeedInterfaceFactory $dataFeedFactory
     * @param FeedCollectionFactory $feedCollectionFactory
     * @param Data\FeedSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ResourceFeed $resource,
        FeedFactory $feedFactory,
        Data\FeedInterfaceFactory $dataFeedFactory,
        FeedCollectionFactory $feedCollectionFactory,
        Data\FeedSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->feedFactory = $feedFactory;
        $this->dataFeedFactory = $dataFeedFactory;
        $this->feedCollectionFactory = $feedCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save Feed data
     *
     * @param FeedInterface $feed
     * @return Feed
     * @throws CouldNotSaveException
     */
    public function save(FeedInterface $feed)
    {
        try {
            $this->resource->save($feed);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the feed: %1',
                $exception->getMessage()
            ));
        }
        return $feed;
    }

    /**
     * Load Feed data by given Feed Identity
     *
     * @param string $feedId
     * @return Feed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($feedId)
    {
        $feed = $this->feedFactory->create();
        $this->resource->load($feed, $feedId);
        if (!$feed->getId()) {
            throw new NoSuchEntityException(__('Feed with id "%1" does not exist.', $feedId));
        }
        $feed->afterLoad();
        return $feed;
    }

    /**
     * Load Feed data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Magento\Cms\Model\ResourceModel\Page\Collection
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->feedCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $feeds = [];
        /** @var Feed $feedModel */
        foreach ($collection as $feedModel) {
            $feedData = $this->dataFeedFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $feedData,
                $feedModel->getData(),
                'Digidirect\Feed\Api\Data\FeedInterface'
            );
            $feeds[] = $this->dataObjectProcessor->buildOutputDataArray(
                $feedData,
                'Digidirect\Feed\Api\Data\FeedInterface'
            );
        }
        $searchResults->setItems($feeds);
        return $searchResults;
    }

    /**
     * Delete Feed
     *
     * @param FeedInterface $feed
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(FeedInterface $feed)
    {
        try {
            $this->resource->delete($feed);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the feed: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete Feed by given Feed Identity
     *
     * @param string $feedId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($feedId)
    {
        return $this->delete($this->getById($feedId));
    }
}
