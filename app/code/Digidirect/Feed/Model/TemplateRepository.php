<?php

namespace Digidirect\Feed\Model;

use Digidirect\Feed\Api\TemplateRepositoryInterface;
use Digidirect\Feed\Api\Data;
use Digidirect\Feed\Api\Data\TemplateInterface;
use Digidirect\Feed\Model\ResourceModel\Template as ResourceTemplate;
use Digidirect\Feed\Model\ResourceModel\Template\CollectionFactory as TemplateCollectionFactory;

use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Reflection\DataObjectProcessor;

use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var ResourceTemplate
     */
    protected $resource;

    /**
     * @var TemplateFactory
     */
    protected $templateFactory;

    /**
     * @var Data\TemplateInterfaceFactory
     */
    protected $dataTemplateFactory;

    /**
     * @var TemplateCollectionFactory
     */
    protected $templateCollectionFactory;

    /**
     * @var Data\TemplateSearchResultsInterfaceFactory
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
     * TemplateRepository constructor.
     * @param ResourceTemplate $resource
     * @param TemplateFactory $templateFactory
     * @param Data\TemplateInterfaceFactory $dataTemplateFactory
     * @param TemplateCollectionFactory $templateCollectionFactory
     * @param Data\TemplateSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     */
    public function __construct(
        ResourceTemplate $resource,
        TemplateFactory $templateFactory,
        Data\TemplateInterfaceFactory $dataTemplateFactory,
        TemplateCollectionFactory $templateCollectionFactory,
        Data\TemplateSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor
    ) {
        $this->resource = $resource;
        $this->templateFactory = $templateFactory;
        $this->dataTemplateFactory = $dataTemplateFactory;
        $this->templateCollectionFactory = $templateCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
    }

    /**
     * Save Template data
     *
     * @param TemplateInterface $template
     * @return Template
     * @throws CouldNotSaveException
     */
    public function save(TemplateInterface $template)
    {
        try {
            $this->resource->save($template);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the template: %1',
                $exception->getMessage()
            ));
        }
        return $template;
    }

    /**
     * Load Template data by given Template Identity
     *
     * @param string $templateId
     * @return Template
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($templateId)
    {
        $template = $this->templateFactory->create();
        $this->resource->load($template, $templateId);
        if (!$template->getId()) {
            throw new NoSuchEntityException(__('Template with id "%1" does not exist.', $templateId));
        }
        $template->afterLoad();
        return $template;
    }

    /**
     * Load Template data collection by given search criteria
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

        $collection = $this->templateCollectionFactory->create();
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
        $templates = [];
        /** @var Template $templateModel */
        foreach ($collection as $templateModel) {
            $templateData = $this->dataTemplateFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $templateData,
                $templateModel->getData(),
                'Digidirect\Template\Api\Data\TemplateInterface'
            );
            $templates[] = $this->dataObjectProcessor->buildOutputDataArray(
                $templateData,
                'Digidirect\Template\Api\Data\TemplateInterface'
            );
        }
        $searchResults->setItems($templates);
        return $searchResults;
    }

    /**
     * Delete Template
     *
     * @param TemplateInterface $template
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(TemplateInterface $template)
    {
        try {
            $this->resource->delete($template);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the template: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * Delete Template by given Template Identity
     *
     * @param string $templateId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($templateId)
    {
        return $this->delete($this->getById($templateId));
    }
}
