<?php

namespace Digidirect\Blog\Model\ResourceModel\Category\Grid;

use Digidirect\Blog\Api\Data\CategoryContentInterface;
use Digidirect\Blog\Model\ResourceModel\Category;
use Digidirect\Blog\Model\StoreContent\DataModifier;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\Search\AggregationInterface;
use Digidirect\Blog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Sql\CategoryInformationJoin;

/**
 * Class Collection
 */
class Collection extends CategoryCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * Collection constructor.
     *
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param CategoryInformationJoin $categoryInformationJoin
     * @param CurrentStoreFetcher $currentStoreFetcher
     * @param DataModifier $dataModifier
     * @param null $mainTable
     * @param string $eventPrefix
     * @param string $eventObject
     * @param string $resourceModel
     * @param string $model
     * @param null $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        CategoryInformationJoin $categoryInformationJoin,
        CurrentStoreFetcher $currentStoreFetcher,
        DataModifier $dataModifier,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $storeManager,
            $categoryInformationJoin,
            $currentStoreFetcher,
            $dataModifier,
            $connection
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
        return $this;
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $this->addParentCategory();
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    protected function addParentCategory()
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from(['category' => $this->getMainTable()], [])
            ->joinLeft(
                ['parent' => $this->getInformationTable()],
                'category.parent_id = parent.' . CategoryContentInterface::CATEGORY_ID,
                ['entity_id' => 'category.entity_id', 'parent_title' => 'parent.name']
            )
            ->where('category.parent_id > 0');
        $result = $connection->fetchPairs($select);
        foreach ($this as $item) {
            if (isset($result[$item->getId()])) {
                $item->setParentTitle($result[$item->getId()]);
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function _beforeLoad()
    {
        $this->getSelect()->joinLeft(
            ['parent' => $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE)],
            'main_table.parent_id = parent.'
            . CategoryContentInterface::CATEGORY_ID
            . ' AND parent.information_store_id = '
            . $this->currentStoreFetcher->getCurrentStoreId(),
            ['parent_title' => 'parent.name']
        );
        return parent::_beforeLoad();
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        $table = $this->getTableNameToSelect($field);
        if (!$table) {
            $table = 'main_table';
        }
        $field = $this->makeFieldSelectWithAlias($table, $field);
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return string
     */
    protected function getInformationTable(): string
    {
        return $this->getTable(Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE);
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->where(
            Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE . '.' . CategoryContentInterface::STORE_ID . '= ?',
            $this->currentStoreFetcher->getCurrentStoreId()
        );
        return $this;
    }
}
