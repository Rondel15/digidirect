<?php
namespace Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity;

use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\AbstractEntity;
use Digidirect\AbstractEntity\Model\ResourceModel\AbstractEntity as AbstractEntityResource;
use Digidirect\AbstractEntity\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\Store;

class Collection extends AbstractCollection
{
    /**
     * Current scope (store Id)
     *
     * @var int
     */
    protected $_storeId;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Eav\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $connection
        );
    }

    /**
     * @return string
     */
    public function getIdFieldName()
    {
        return AbstractEntityResource::ID_FIELD_NAME;
    }

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AbstractEntity::class, AbstractEntityResource::class);
    }

    /**
     * Retrieve Entity Primary Key
     *
     * @param \Magento\Eav\Model\Entity\AbstractEntity $entity
     * @return string
     */
    protected function getEntityPkName(\Magento\Eav\Model\Entity\AbstractEntity $entity)
    {
        return $entity->getLinkField();
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->setStoreId($this->_storeManager->getStore($store)->getId());
        return $this;
    }

    /**
     * Set store scope
     *
     * @param int|string|\Magento\Store\Api\Data\StoreInterface $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        if ($storeId instanceof StoreInterface) {
            $storeId = $storeId->getId();
        }
        $this->_storeId = (int)$storeId;
        return $this;
    }

    /**
     * Return current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->_storeId === null) {
            $this->setStoreId($this->_storeManager->getStore()->getId());
        }
        return $this->_storeId;
    }

    /**
     * Retrieve default store id
     *
     * @return int
     */
    public function getDefaultStoreId()
    {
        return Store::DEFAULT_STORE_ID;
    }

    /**
     * Retrieve attributes load select
     *
     * @param string $table
     * @param array|int $attributeIds
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _getLoadAttributesSelect($table, $attributeIds = [])
    {
        if (empty($attributeIds)) {
            $attributeIds = $this->_selectAttributes;
        }
        $storeId = $this->getStoreId();
        $connection = $this->getConnection();

        $entityTable = $this->getEntity()->getEntityTable();
        $indexList = $connection->getIndexList($entityTable);
        $entityIdField = $indexList[$connection->getPrimaryKeyName($entityTable)]['COLUMNS_LIST'][0];

        if ($storeId !== null) {
            $joinCondition = [
                't_s.attribute_id = t_d.attribute_id',
                "t_s.{$entityIdField} = t_d.{$entityIdField}",
                $connection->quoteInto('t_s.store_id = ?', $storeId),
            ];

            $select = $connection->select()->from(
                ['t_d' => $table],
                ['attribute_id']
            )->join(
                ['e' => $entityTable],
                "e.{$entityIdField} = t_d.{$entityIdField}",
                ['e.entity_id']
            )->where(
                "e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                't_d.attribute_id IN (?)',
                $attributeIds
            )->joinLeft(
                ['t_s' => $table],
                implode(' AND ', $joinCondition),
                []
            )->where(
                't_d.store_id = ?',
                $connection->getIfNullSql('t_s.store_id', Store::DEFAULT_STORE_ID)
            );
        } else {
            $select = $connection->select()->from(
                ['t_d' => $table],
                ['attribute_id']
            )->join(
                ['e' => $entityTable],
                "e.{$entityIdField} = t_d.{$entityIdField}",
                ['e.entity_id']
            )->where(
                "e.entity_id IN (?)",
                array_keys($this->_itemsById)
            )->where(
                'attribute_id IN (?)',
                $attributeIds
            )->where(
                'store_id = ?',
                $this->getDefaultStoreId()
            );
        }
        return $select;
    }

    /**
     * @param \Magento\Framework\DB\Select $select
     * @param string $table
     * @param string $type
     * @return \Magento\Framework\DB\Select
     */
    protected function _addLoadAttributesSelectValues($select, $table, $type)
    {
        $storeId = $this->getStoreId();
        if ($storeId) {
            $connection = $this->getConnection();
            $valueExpr = $connection->getCheckSql('t_s.value_id IS NULL', 't_d.value', 't_s.value');

            $select->columns(
                ['default_value' => 't_d.value', 'store_value' => 't_s.value', 'value' => $valueExpr]
            );
        } else {
            $select = parent::_addLoadAttributesSelectValues($select, $table, $type);
        }
        return $select;
    }

    /**
     * Adding join statement to collection select instance
     *
     * @param string $method
     * @param object $attribute
     * @param string $tableAlias
     * @param array $condition
     * @param string $fieldCode
     * @param string $fieldAlias
     * @return \Magento\Eav\Model\Entity\Collection\AbstractCollection
     */
    protected function _joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias)
    {
        if (isset($this->_joinAttributes[$fieldCode]['store_id'])) {
            $storeId = $this->_joinAttributes[$fieldCode]['store_id'];
        } else {
            $storeId = $this->getStoreId();
        }

        $connection = $this->getConnection();

        if ($storeId != $this->getDefaultStoreId() && !$attribute->isScopeGlobal()) {
            /**
             * Add joining default value for not default store
             * if value for store is null - we use default value
             */
            $defCondition = '(' . implode(') AND (', $condition) . ')';
            $defAlias = $tableAlias . '_default';
            $defAlias = $this->getConnection()->getTableName($defAlias);
            $defFieldAlias = str_replace($tableAlias, $defAlias, $fieldAlias);
            $tableAlias = $this->getConnection()->getTableName($tableAlias);

            $defCondition = str_replace($tableAlias, $defAlias, $defCondition);
            $defCondition .= $connection->quoteInto(
                " AND " . $connection->quoteColumnAs("{$defAlias}.store_id", null) . " = ?",
                $this->getDefaultStoreId()
            );

            $this->getSelect()->{$method}(
                [$defAlias => $attribute->getBackend()->getTable()],
                $defCondition,
                []
            );

            $method = 'joinLeft';
            $fieldAlias = $this->getConnection()->getCheckSql(
                "{$tableAlias}.value_id > 0",
                $fieldAlias,
                $defFieldAlias
            );
            $this->_joinAttributes[$fieldCode]['condition_alias'] = $fieldAlias;
            $this->_joinAttributes[$fieldCode]['attribute'] = $attribute;
        } else {
            $storeId = $this->getDefaultStoreId();
        }
        $condition[] = $connection->quoteInto(
            $connection->quoteColumnAs("{$tableAlias}.store_id", null) . ' = ?',
            $storeId
        );
        return parent::_joinAttributeToSelect($method, $attribute, $tableAlias, $condition, $fieldCode, $fieldAlias);
    }

    /**
     * @param int $attributeSetId
     * @return void
     */
    public function deleteByAttributeSetId($attributeSetId)
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getEntity()->getEntityTable(),
            $connection->quoteInto(AbstractEntityInterface::ATTRIBUTE_SET_ID . ' = ?', $attributeSetId)
        );
    }

    /**
     * @return $this
     */
    public function joinAttributeSet()
    {
        $this->getSelect()->join(
            ['set' => $this->getTable('eav_attribute_set')],
            new \Zend_Db_Expr('`e`.`attribute_set_id` = `set`.`attribute_set_id`'),
            ['entity' => 'attribute_set_name']
        );
        return $this;
    }

    /**
     * @return array
     */
    public function getUsedInGridAttributes()
    {
        $select = $this->getConnection()->select()
            ->from(
                ['main' => $this->getTable('digidirect_abstractentity_eav_attribute')],
                []
            )
            ->join(
                ['eav' => $this->getTable('eav_attribute')],
                'main.attribute_id = eav.attribute_id',
                'attribute_code'
            )
            ->where(Attribute::KEY_IS_USED_IN_GRID . ' = ?', 1)
            ->order('main.position ASC');

        return $this->getConnection()->fetchCol($select);
    }
}
