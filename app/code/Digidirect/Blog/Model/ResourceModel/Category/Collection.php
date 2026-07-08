<?php

namespace Digidirect\Blog\Model\ResourceModel\Category;

use Digidirect\Blog\Model\CurrentStoreFetcher;
use Digidirect\Blog\Model\ResourceModel\Category;
use Digidirect\Blog\Model\StoreContent\DataModifier;
use Digidirect\Blog\Sql\CategoryInformationJoin;
use Magento\Store\Model\Store;

/**
 * Class Collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CategoryInformationJoin
     */
    protected $categoryInformationJoin;

    /**
     * @var CurrentStoreFetcher
     */
    protected $currentStoreFetcher;

    /**
     * @var array
     */
    protected $columnsByTableRequiredIfNull = [];

    /**
     * @var DataModifier
     */
    protected $dataModifier;

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
     * @param null $connection
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
        $connection = null
    ) {
        $this->dataModifier = $dataModifier;
        $this->currentStoreFetcher = $currentStoreFetcher;
        $this->categoryInformationJoin = $categoryInformationJoin;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection);
        $this->_storeManager = $storeManager;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\Blog\Model\Category', 'Digidirect\Blog\Model\ResourceModel\Category');
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad()
    {
        $this->_performAfterLoad(Category::STORE_RELATION_TABLE, 'category_id');
        $this->changeData();
        return parent::_afterLoad();
    }

    /**
     * @return $this
     */
    protected function changeData()
    {
        foreach ($this->getItems() as $key => $item) {
            $data = $item->getData();
            $data = $this->dataModifier->modifyData($data, $this->categoryInformationJoin->getDefaultContentPrefix());
            $item->setData($data);
        }

        return $this;
    }

    /**
     * Perform operations after collection load
     *
     * @param string $tableName
     * @param string|null $linkField
     * @return void
     */
    protected function _performAfterLoad($tableName, $linkField)
    {
        $linkedIds = $this->getColumnValues('entity_id');
        if (count($linkedIds)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['category_store' => $this->getTable($tableName)])
                ->where('category_store.' . $linkField . ' IN (?)', $linkedIds);
            $result = $connection->fetchAll($select);

            if ($result) {
                $storesData = [];
                foreach ($result as $storeData) {
                    $storesData[$storeData[$linkField]][] = $storeData['store_id'];
                }

                foreach ($this as $item) {
                    $linkedId = $item->getData('entity_id');
                    if (!isset($storesData[$linkedId])) {
                        continue;
                    }
                    $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $storesData[$linkedId], true);
                    if ($storeIdKey !== false) {
                        $stores = $this->_storeManager->getStores(false, true);
                        $storeId = current($stores)->getId();
                        $storeCode = key($stores);
                    } else {
                        $storeId = current($storesData[$linkedId]);
                        $storeCode = $this->_storeManager->getStore($storeId)->getCode();
                    }
                    $item->setData('_first_store_id', $storeId);
                    $item->setData('store_code', $storeCode);
                    $item->setData('store_id', $storesData[$linkedId]);
                }
            }
        }
    }

    /**
     * @return $this
     */
    protected function prepareDdl()
    {
        $array = [
            Category::DIGIDIRECT_BLOG_CATEGORY_INFORMATION_TABLE,
        ];
        try {
            foreach ($array as $table) {
                $this->columnsByTableRequiredIfNull[$table] = array_keys($this->getConnection()->describeTable($table));
            }
            return $this;
        } catch (\Throwable $exception) {
            return $this;
        }
    }

    /**
     * @return $this
     */
    protected function _initSelect()
    {
        $this->prepareDdl();
        parent::_initSelect();
        $this->categoryInformationJoin->join(
            $this->getSelect(),
            $this->currentStoreFetcher->getCurrentStoreId(),
            'main_table'
        );

        if (!$this->currentStoreFetcher->getIsDefault()) {
            $this->categoryInformationJoin->joinDefault(
                $this->getSelect(),
                $this->currentStoreFetcher->getDefaultStoreId(),
                'main_table'
            );
        }
        return $this;
    }

    /**
     * @param string $field
     * @return int|null|string
     */
    protected function getTableNameToSelect($field)
    {
        foreach ($this->columnsByTableRequiredIfNull as $tableName => $columns) {
            if (in_array($field, $columns)) {
                return $tableName;
            }
        }

        return null;
    }

    /**
     * @param array|string $field
     * @param null $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->currentStoreFetcher->getIsDefault()) {
            parent::addFieldToFilter($field, $condition);
            return $this;
        }
        if (is_array($field)) {
            $conditions = [];
            foreach ($field as $key => $value) {
                $table = $this->getTableNameToSelect($key);
                $value = $this->getIfNullCondition($value, $table);
                $conditions[] = $this->_translateCondition($value, isset($condition[$key]) ? $condition[$key] : null);
            }

            $resultCondition = '(' . implode(') ' . \Magento\Framework\DB\Select::SQL_OR . ' (', $conditions) . ')';
        } else {
            $table = $this->getTableNameToSelect($field);
            $field = $this->getIfNullCondition($field, $table);
            $resultCondition = $this->_translateCondition($field, $condition);
        }

        $this->getSelect()->where($resultCondition, null, \Magento\Framework\DB\Select::TYPE_CONDITION);
        return $this;
    }

    /**
     * @param mixed $field
     * @return array|int|null|string
     */
    protected function modifyFilter($field)
    {
        $callback = function ($field) {
            return $this->getTableNameToSelect($field);
        };
        if (is_array($field)) {
            foreach ($field as $key => $value) {
                $newKey = $callback($key);
                $field[$newKey] = $value;
                unset($field[$key]);
            }
        } else {
            $field = $callback($field);
        }

        return $field;
    }

    /**
     * @param string $field
     * @param null $table
     * @return \Zend_Db_Expr
     */
    protected function getIfNullCondition($field, $table = null)
    {
        if (null === $table) {
            return $field;
        }
        $expr = $this->makeFieldSelectWithAlias($table, $field);
        return $this->getConnection()->getIfNullSql(
            $expr,
            CategoryInformationJoin::DEFAULT_STORE_COLUMN_PREFIX . $expr
        );
    }

    /**
     * @param string $alias
     * @param string $field
     * @return string
     */
    protected function makeFieldSelectWithAlias(string $alias, string $field): string
    {
        return $alias . '.' . $field;
    }

    /**
     * Add filter by categories
     *
     * @param array $categoryIds
     * @param bool $exclude
     * @return $this
     */
    public function addCategoryIdsFilter($categoryIds, $exclude = false)
    {
        $this->addFieldToFilter('main_table.entity_id', [$exclude ? 'nin' : 'in' => $categoryIds]);
        return $this;
    }
}
