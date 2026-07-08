<?php

namespace Digidirect\Feed\Model;

use Digidirect\Feed\Api\RuleRepositoryInterface;
use Digidirect\Feed\Api\Data;
use Digidirect\Feed\Api\Data\RuleInterface;
use Digidirect\Feed\Model\ResourceModel\Rule as ResourceRule;
use Digidirect\Feed\Model\ResourceModel\Rule\CollectionFactory as RuleCollectionFactory;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class RuleRepository implements RuleRepositoryInterface
{
    /**
     * @var ResourceRule
     */
    protected $resource;

    /**
     * @var RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var Data\RuleInterfaceFactory
     */
    protected $dataRuleFactory;

    /**
     * @var RuleCollectionFactory
     */
    protected $ruleCollectionFactory;

    /**
     * @var Data\RuleSearchResultsInterfaceFactory
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
     * RuleRepository constructor.
     * @param ResourceRule $resource
     * @param RuleFactory $ruleFactory
     * @param Data\RuleInterfaceFactory $dataRuleFactory
     * @param RuleCollectionFactory $ruleCollectionFactory
     * @param Data\RuleSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ResourceRule $resource,
        RuleFactory $ruleFactory,
        Data\RuleInterfaceFactory $dataRuleFactory,
        RuleCollectionFactory $ruleCollectionFactory,
        Data\RuleSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->ruleFactory = $ruleFactory;
        $this->dataRuleFactory = $dataRuleFactory;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save Rule data
     *
     * @param RuleInterface $rule
     * @return Rule
     * @throws CouldNotSaveException
     */
    public function save(RuleInterface $rule)
    {
        try {
            $this->resource->save($rule);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the rule: %1',
                $exception->getMessage()
            ));
        }
        return $rule;
    }

    /**
     * Load Rule data by given Rule Identity
     *
     * @param string $ruleId
     * @return Rule
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($ruleId)
    {
        $rule = $this->ruleFactory->create();
        $this->resource->load($rule, $ruleId);
        if (!$rule->getId()) {
            throw new NoSuchEntityException(__('Rule with id "%1" does not exist.', $ruleId));
        }
        return $rule;
    }

    /**
     * Load Rule data collection by given search criteria
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

        $collection = $this->ruleCollectionFactory->create();
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
        $rules = [];
        /** @var Rule $ruleModel */
        foreach ($collection as $ruleModel) {
            $ruleData = $this->dataRuleFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $ruleData,
                $ruleModel->getData(),
                'Digidirect\Feed\Api\Data\RuleInterface'
            );
            $rules[] = $this->dataObjectProcessor->buildOutputDataArray(
                $ruleData,
                'Digidirect\Feed\Api\Data\RuleInterface'
            );
        }
        $searchResults->setItems($rules);
        return $searchResults;
    }

    /**
     * Delete Rule
     *
     * @param RuleInterface $rule
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(RuleInterface $rule)
    {
        try {
            $this->resource->delete($rule);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the rule: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete Rule by given Rule Identity
     *
     * @param string $ruleId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($ruleId)
    {
        return $this->delete($this->getById($ruleId));
    }
}
