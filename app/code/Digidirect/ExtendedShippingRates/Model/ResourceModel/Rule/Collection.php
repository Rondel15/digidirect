<?php
namespace Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule;

use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Rule\Model\ResourceModel\Rule\Collection\AbstractCollection
{
    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var string
     */
    protected $_idFieldName = 'rule_id';

    /**
     * Store associated with rule entities information map
     *
     * @var array
     */
    protected $_associatedEntitiesMap = [
        'store' => [
            'associations_table' => \Digidirect\ExtendedShippingRates\Model\Rule::STORE_TABLE_NAME,
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'store_id',
        ],
        'customer_group' => [
            'associations_table' => \Digidirect\ExtendedShippingRates\Model\Rule::CUSTOMER_GROUP_TABLE_NAME,
            'rule_id_field' => 'rule_id',
            'entity_id_field' => 'customer_group_id',
        ],
    ];

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->date = $date;
        $this->storeManager = $storeManager;
    }

    /**
     * Set resource model and determine field mapping
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\ExtendedShippingRates\Model\Rule', 'Digidirect\ExtendedShippingRates\Model\ResourceModel\Rule');
        $this->_map['fields']['rule_id'] = 'main_table.rule_id';
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Filter collection by specified store, customer group, date.
     * Filter collection to use only active rules.
     * Involved sorting by sort_order column.
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @param string|null $now
     * @use $this->addStoreGroupDateFilter()
     * @return $this
     */
    public function setValidationFilter($storeId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('validation_filter')) {
            $this->addStoreGroupDateFilter($storeId, $customerGroupId, $now);
            $this->setOrder('sort_order', self::SORT_ORDER_DESC);
            $this->setFlag('validation_filter', true);
        }

        return $this;
    }

    /**
     * Filter collection by store(s), customer group(s) and date.
     * Filter collection to only active rules.
     * Sorting is not involved
     *
     * @param int $storeId
     * @param int $customerGroupId
     * @param string|null $now
     * @use $this->addStoreFilter()
     * @return $this
     */
    public function addStoreGroupDateFilter($storeId, $customerGroupId, $now = null)
    {
        if (!$this->getFlag('store_group_date_filter')) {
            if ($now === null) {
                $now = $this->date->date()->format('Y-m-d');
            }

            $this->addStoreFilter($storeId);
            $this->addCustomerGroupFilter($customerGroupId);
            $this->addDateFilter($now);
            $this->addIsActiveFilter();

            $this->setFlag('store_group_date_filter', true);
        }

        return $this;
    }

    /**
     * From date or to date filter
     *
     * @param string $now
     * @return $this
     */
    public function addDateFilter($now)
    {
        $this->getSelect()->where(
            'from_date is null or from_date <= ?',
            $now
        )->where(
            'to_date is null or to_date >= ?',
            $now
        );

        return $this;
    }

    /**
     * Customer group filter
     *
     * @param string $customerGroupId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addCustomerGroupFilter($customerGroupId)
    {
        $entityInfo = $this->_getAssociatedEntityInfo('customer_group');
        $connection = $this->getConnection();
        $this->getSelect()->joinInner(
            ['customer_group_ids' => $this->getTable($entityInfo['associations_table'])],
            $connection->quoteInto(
                'main_table.' .
                $entityInfo['rule_id_field'] .
                ' = customer_group_ids.' .
                $entityInfo['rule_id_field'] .
                ' AND customer_group_ids.' .
                $entityInfo['entity_id_field'] .
                ' = ?',
                (int)$customerGroupId
            ),
            []
        );

        return $this;
    }

    /**
     * Limit rules collection by specific stores
     *
     * @param int|int[]|Store $storeId
     * @return $this
     */
    public function addStoreFilter($storeId)
    {
        $this->joinStoreTable();
        if ($storeId instanceof Store) {
            $storeId = $storeId->getId();
        }

        parent::addFieldToFilter(
            'store.store_id',
            [
                ['eq' => $storeId],
                ['eq' => '0']
            ]
        );

        $this->getSelect()->distinct(true);

        return $this;
    }

    /**
     * Provide support for store id filter
     *
     * @param string $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field == 'store') {
            return $this->addStoreFilter($condition);
        }

        parent::addFieldToFilter($field, $condition);
        return $this;
    }

    /**
     * Join store table
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    protected function joinStoreTable()
    {
        $entityInfo = $this->_getAssociatedEntityInfo('store');
        if (!$this->getFlag('is_store_table_joined')) {
            $this->setFlag('is_store_table_joined', true);
            $this->getSelect()->joinLeft(
                ['store' => $this->getTable($entityInfo['associations_table'])],
                'main_table.' . $entityInfo['rule_id_field'] . ' = store.' . $entityInfo['rule_id_field'],
                []
            );
        }
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->joinStoreTable();
        $this->addStoreData();
        return parent::_afterLoad();
    }

    /**
     * @param \Magento\Framework\DataObject $item
     * @return \Magento\Framework\DataObject
     */
    protected function beforeAddLoadedItem(\Magento\Framework\DataObject $item)
    {
        parent::beforeAddLoadedItem($item);
        if ($item instanceof \Magento\Framework\Model\AbstractModel) {
            $this->getResource()->unserializeFields($item);
        }
        return $item;
    }

    /**
     * Adds store data to the items
     *
     * @return void
     */
    protected function addStoreData()
    {
        $ids = $this->getColumnValues('rule_id');
        if (!empty($ids)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(
                [
                    'digidirect_extendedshippingrates_store' => $this->getTable(
                        \Digidirect\ExtendedShippingRates\Model\Rule::STORE_TABLE_NAME
                    )
                ]
            )->where(\Digidirect\ExtendedShippingRates\Model\Rule::STORE_TABLE_NAME . '.rule_id IN (?)', $ids);

            $result = $connection->fetchAll($select);
            if ($result) {
                $data = [];
                foreach ($result as $storeData) {
                    $data[$storeData['rule_id']][] = $storeData['store_id'];
                }
                $this->addStoresDataToItems($data);
            }
        }
    }

    /**
     * Add stores to each item
     *
     * @param array $data
     * @return void
     */
    protected function addStoresDataToItems($data)
    {
        foreach ($this as $item) {
            $linkedId = $item->getData('rule_id');
            if (!isset($data[$linkedId])) {
                continue;
            }

            $storeIdKey = array_search(Store::DEFAULT_STORE_ID, $data[$linkedId], true);
            if ($storeIdKey !== false) {
                $stores = $this->storeManager->getStores(false, true);
                $storeId = current($stores)->getId();
                $storeCode = key($stores);
            } else {
                $storeId = current($data[$linkedId]);
                $store = $this->storeManager->getStore($storeId);
                $storeCode = $store->getCode();
            }

            $item->setData('_first_store_id', $storeId)
                ->setData('store_code', $storeCode)
                ->setData('store_id', $data[$linkedId]);
        }
    }
}
