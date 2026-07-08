<?php

namespace Digidirect\CheckoutFields\Model;

use Digidirect\CheckoutFields\Api\Data\PdpFieldValueInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Digidirect\CheckoutFields\Api\PdpFieldValueRepositoryInterface;
use Digidirect\CheckoutFields\Model\PdpFieldValueFactory;
use Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue as PdpFieldValueResource;
use Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue\Collection;
use Digidirect\CheckoutFields\Model\ResourceModel\PdpFieldValue\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;

/**
 * Class PdpFieldValueRepository
 * @package Digidirect\CheckoutFields\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PdpFieldValueRepository implements PdpFieldValueRepositoryInterface
{
    /**
     * @var PdpFieldValueResource
     */
    protected $_resource;

    /**
     * @var PdpFieldValueFactory
     */
    protected $_modelFactory;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $_searchResultsFactory;

    /**
     * @var SortOrderBuilder
     */
    protected $_sortOrderBuilder;

    /**
     * PdpFieldValueRepository constructor.
     * @param \Digidirect\CheckoutFields\Model\PdpFieldValueFactory $modelFactory
     * @param PdpFieldValueResource $resource
     * @param CollectionFactory $collectionFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        PdpFieldValueFactory $modelFactory,
        PdpFieldValueResource $resource,
        CollectionFactory $collectionFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SearchResultsInterfaceFactory $searchResultsFactory,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->_modelFactory = $modelFactory;
        $this->_resource = $resource;
        $this->_collectionFactory = $collectionFactory;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * Get all rows
     *
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();

        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }

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

        /** @var SearchResultsInterface $searchResults */
        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * Get values by Quote ID
     *
     * @param int $quoteId
     * @param string $code
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getListByQuoteId($quoteId, $code)
    {
        $sortOrder = $this->_sortOrderBuilder
            ->setField(PdpFieldValueInterface::ENTITY_ID)
            ->setDirection(SortOrder::SORT_DESC)
            ->create();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(PdpFieldValueInterface::QUOTE_ID, $quoteId)
            ->addFilter(PdpFieldValueInterface::FIELD_CODE, $code)
            ->addSortOrder($sortOrder)
            ->setPageSize(1)
            ->create();

        $items = $this->getList($searchCriteria)->getItems();
        return $items;
    }

    /**
     * @param PdpFieldValue $object
     * @return PdpFieldValue
     * @throws CouldNotSaveException
     */
    public function save(PdpFieldValue $object)
    {
        try {
            $this->cleanOldRows($object);
            $this->_resource->save($object);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the Custom Field value: %1',
                $exception->getMessage()
            ));
        }
        return $object;
    }

    /**
     * @param PdpFieldValue $object
     * @return void
     * @throws \Exception
     */
    public function cleanOldRows($object)
    {
        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(PdpFieldValueInterface::QUOTE_ID, $object->getQuoteId())
            ->addFilter(PdpFieldValueInterface::PRODUCT_ID, $object->getProductId())
            ->addFilter(PdpFieldValueInterface::FIELD_CODE, $object->getFieldCode())
            ->create();
        $items = $this->getList($searchCriteria)->getItems();
        foreach ($items as $item) {
            $this->_resource->delete($item);
        }
    }
}
