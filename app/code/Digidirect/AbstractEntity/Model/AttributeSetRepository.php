<?php
namespace Digidirect\AbstractEntity\Model;

use Digidirect\AbstractEntity\Api\AttributeSetRepositoryInterface;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity\CollectionFactory as AbstractEntityCollectionFactory;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set as AttributeSetResource;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SortOrder;

/**
 * Class AttributeSetRepository
 * @package Digidirect\AbstractEntity\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeSetRepository extends \Magento\Eav\Model\AttributeSetRepository
    implements AttributeSetRepositoryInterface
{
    /**
     * @var AttributeSetResource
     */
    private $attributeSetResource;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var \Magento\Eav\Api\Data\AttributeSetSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var AbstractEntityCollectionFactory
     */
    protected $abstractEntityCollectionFactory;

    /**
     * @var array
     */
    protected $_relationPool;

    /**
     * AttributeSetRepository constructor.
     * @param AttributeSetResource $attributeSetResource
     * @param AttributeSetFactory $attributeSetFactory
     * @param CollectionFactory $collectionFactory
     * @param EavConfig $eavConfig
     * @param AttributeSetSearchResultsInterfaceFactory $searchResultFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param AbstractEntityCollectionFactory $abstractEntityCollectionFactory
     * @param array $relationPool
     * @codeCoverageIgnore
     */
    public function __construct(
        AttributeSetResource $attributeSetResource,
        AttributeSetFactory $attributeSetFactory,
        CollectionFactory $collectionFactory,
        EavConfig $eavConfig,
        AttributeSetSearchResultsInterfaceFactory $searchResultFactory,
        JoinProcessorInterface $joinProcessor,
        AbstractEntityCollectionFactory $abstractEntityCollectionFactory,
        array $relationPool = []
    ) {
        $this->attributeSetResource = $attributeSetResource;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->collectionFactory = $collectionFactory;
        $this->eavConfig = $eavConfig;
        $this->searchResultsFactory = $searchResultFactory;
        $this->abstractEntityCollectionFactory = $abstractEntityCollectionFactory;
        $this->_relationPool = $relationPool;
        parent::__construct(
            $attributeSetResource,
            $attributeSetFactory,
            $collectionFactory,
            $eavConfig,
            $searchResultFactory,
            $joinProcessor
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria = null)
    {
        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection);
        $collection->setEntityTypeFilter($this->eavConfig->getEntityType(AbstractEntity::ENTITY_TYPE)->getId());
        if ($searchCriteria !== null) {
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                }
            }

            $sortOrders = $searchCriteria->getSortOrders();
            if ($sortOrders) {
                /** @var SortOrder $sortOrder */
                foreach ($sortOrders as $sortOrder) {
                    $collection->addOrder(
                        $sortOrder->getField(),
                        ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? SortOrder::SORT_ASC : SortOrder::SORT_DESC
                    );
                }
            }
        }

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        if ($searchCriteria !== null) {
            $searchResults->setSearchCriteria($searchCriteria);
            $collection->setCurPage($searchCriteria->getCurrentPage());
            $collection->setPageSize($searchCriteria->getPageSize());
        }

        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(AttributeSetInterface $attributeSet)
    {
        if (parent::delete($attributeSet)) {
            $collection = $this->abstractEntityCollectionFactory->create();
            $collection->deleteByAttributeSetId($attributeSet->getAttributeSetId());
        }
        return true;
    }

    /**
     * @param int $entityId
     * @param array $data
     * @return void
     */
    public function processRelations($entityId, $data)
    {
        foreach ($this->_relationPool as $relationInstance) {
            if ($relationInstance instanceof \Digidirect\AbstractEntity\Model\RelationInterface) {
                $relationInstance->processRelation($entityId, $data);
            }
        }
    }
}
