<?php

namespace Digidirect\ExtendedCartPriceRules\Model;

use Digidirect\ExtendedCartPriceRules\Api\Data\ExtendedCartPriceRuleInterface;
use Digidirect\ExtendedCartPriceRules\Api\ExtendedCartPriceRuleRepositoryInterface;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule as ExtendedCartPriceRuleResource;
use Digidirect\ExtendedCartPriceRules\Model\ResourceModel\ExtendedCartPriceRule\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class ExtendedCartPriceRuleRepository implements ExtendedCartPriceRuleRepositoryInterface
{
    /**
     * @var ExtendedCartPriceRuleResource
     */
    protected $resource;

    /**
     * @var ExtendedCartPriceRuleFactory
     */
    protected $extendedCartPriceRuleFactory;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * ExtendedCartPriceRuleRepository constructor.
     *
     * @param ExtendedCartPriceRuleResource $resource
     * @param ExtendedCartPriceRuleFactory $extendedCartPriceRuleFactory
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     */
    public function __construct(
        ExtendedCartPriceRuleResource $resource,
        ExtendedCartPriceRuleFactory $extendedCartPriceRuleFactory,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->resource = $resource;
        $this->extendedCartPriceRuleFactory = $extendedCartPriceRuleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param ExtendedCartPriceRuleInterface $extendedCartPriceRule
     * @return ExtendedCartPriceRuleInterface
     * @throws CouldNotSaveException
     */
    public function save(ExtendedCartPriceRuleInterface $extendedCartPriceRule)
    {
        try {
            $this->resource->save($extendedCartPriceRule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__('Unable to save ExtendedCartPriceRule'), $exception);
        }

        return $extendedCartPriceRule;
    }

    /**
     * @param int $id
     * @return ExtendedCartPriceRuleInterface
     * @throws NoSuchEntityException
     */
    public function get($id)
    {
        $extendedCartPriceRule = $this->extendedCartPriceRuleFactory->create();
        $this->resource->load($extendedCartPriceRule, $id);
        if (!$extendedCartPriceRule->getId()) {
            throw new NoSuchEntityException(__('ExtendedCartPriceRule with id "%1" does not exist.', $id));
        }

        return $extendedCartPriceRule;
    }

    /**
     * @param SearchCriteriaInterface $criteria
     * @return SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->collectionFactory->create();
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
        $searchResults->setItems($collection->getItems());

        return $searchResults;
    }

    /**
     * @param ExtendedCartPriceRuleInterface $extendedCartPriceRule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ExtendedCartPriceRuleInterface $extendedCartPriceRule)
    {
        try {
            $this->resource->delete($extendedCartPriceRule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Unable to remove ExtendedCartPriceRule with id "%1"', $extendedCartPriceRule->getRuleId()),
                $exception
            );
        }

        return true;
    }
}
