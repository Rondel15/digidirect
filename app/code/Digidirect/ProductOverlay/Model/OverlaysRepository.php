<?php

namespace Digidirect\ProductOverlay\Model;

use Digidirect\ProductOverlay\Api\Data;
use Digidirect\ProductOverlay\Api\OverlayRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Digidirect\ProductOverlay\Model\ResourceModel\Overlays as ResourceOverlay;
use Digidirect\ProductOverlay\Model\ResourceModel\Overlays\CollectionFactory as OverlayCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class OverlaysRepository
 * @package Digidirect\ProductOverlay\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OverlaysRepository implements OverlayRepositoryInterface
{
    /**
     * @var ResourceOverlay
     */
    protected $resource;

    /**
     * @var OverlaysFactory
     */
    protected $overlayFactory;

    /**
     * @var OverlayCollectionFactory
     */
    protected $overlayCollectionFactory;

    /**
     * @var Data\OverlaySearchResultsInterfaceFactory
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
     * @var \Digidirect\ProductOverlay\Api\Data\OverlayInterfaceFactory
     */
    protected $dataOverlayFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * OverlaysRepository constructor.
     * @param ResourceOverlay $resource
     * @param OverlaysFactory $overlayFactory
     * @param Data\OverlayInterfaceFactory $dataOverlayFactory
     * @param OverlayCollectionFactory $overlayCollectionFactory
     * @param Data\OverlaySearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceOverlay $resource,
        OverlaysFactory $overlayFactory,
        Data\OverlayInterfaceFactory $dataOverlayFactory,
        OverlayCollectionFactory $overlayCollectionFactory,
        Data\OverlaySearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource                 = $resource;
        $this->overlayFactory           = $overlayFactory;
        $this->overlayCollectionFactory = $overlayCollectionFactory;
        $this->searchResultsFactory     = $searchResultsFactory;
        $this->dataObjectHelper         = $dataObjectHelper;
        $this->dataOverlayFactory       = $dataOverlayFactory;
        $this->dataObjectProcessor      = $dataObjectProcessor;
        $this->storeManager             = $storeManager;
    }

    /**
     * Save Overlay data
     *
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay
     * @return Overlays
     * @throws CouldNotSaveException
     */
    public function save(\Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $overlay->setStoreId($storeId);
        try {
            $this->resource->save($overlay);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $overlay;
    }

    /**
     * Load Overlay data by given Overlay Identity
     *
     * @param string $overlayId
     * @return Overlays
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($overlayId)
    {
        $overlay = $this->overlayFactory->create();
        $overlay->load($overlayId);
        if (!$overlay->getId()) {
            throw new NoSuchEntityException(__('Overlay with id "%1" does not exist.', $overlayId));
        }
        return $overlay;
    }

    /**
     * Load Overlay data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @param bool $outputDataArray
     * @return \Digidirect\ProductOverlay\Api\Data\OverlaySearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria, $outputDataArray = true)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->overlayCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() === 'store_id') {
                    $collection->addStoreFilter($filter->getValue(), false);
                    continue;
                }
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
        $overlays = [];
        /** @var Overlays $overlayModel */
        foreach ($collection->getItems() as $overlayModel) {
            if ($outputDataArray) {
                $overlayData = $this->dataOverlayFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $overlayData,
                    $overlayModel->getData(),
                    'Digidirect\ProductOverlay\Api\Data\OverlayInterface'
                );
                $overlays[] = $this->dataObjectProcessor->buildOutputDataArray(
                    $overlayData,
                    'Digidirect\ProductOverlay\Api\Data\OverlayInterface'
                );
            } else {
                $overlays[] = $overlayModel;
            }
        }
        $searchResults->setItems($overlays);
        return $searchResults;
    }

    /**
     * Delete Overlay
     *
     * @param \Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Digidirect\ProductOverlay\Api\Data\OverlayInterface $overlay)
    {
        try {
            $this->resource->delete($overlay);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Overlay by given Overlay Identity
     *
     * @param string $overlayId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($overlayId)
    {
        return $this->delete($this->getById($overlayId));
    }

    /**
     * @param int $priority
     * @return array
     */
    public function getByPriority($priority)
    {
        return $this->resource->loadByPriority($priority);
    }
}
