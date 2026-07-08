<?php

namespace Digidirect\AbstractEntity\Model\ResourceModel;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Digidirect\AbstractEntity\Helper\Data as Helper;
use Digidirect\AbstractEntity\Helper\Config as Config;
use Digidirect\AbstractEntity\Api\Data\AbstractEntityInterface;
use Digidirect\AbstractEntity\Model\Config\Source\FulltextSearchPattern;

/**
 * Class MyStoreIndex
 *
 * @package Digidirect\MyStoreWidget\Model\ResourceModel
 */
class AbstractEntityIndex extends AbstractDb
{
    const TABLE_NAME = 'digidirect_abstractentity_index_';
    const SELECT = 'select';
    const TABLE_POSTFIX = 'table_postfix';
    const ENGINE = 'engine';
    const FULTEXT_COLUMNS = 'fulltext_columns';

    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var array
     */
    protected $rangeAttributes = [];

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * AbstractEntityIndex constructor.
     *
     * @param Context $context
     * @param ManagerInterface $eventManager
     * @param Helper $helper
     * @param array $rangeAttributes
     * @param null $connectionName
     * @param Config $config
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        Helper $helper,
        $rangeAttributes = [],
        $connectionName = null,
        Config $config = null
    ) {
        parent::__construct($context, $connectionName);
        $this->eventManager = $eventManager;
        $this->rangeAttributes = $rangeAttributes;
        $this->helper = $helper;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param Select $select
     * @param string $tablePostfix
     * @param int $storeId
     * @param string $engine
     * @param array $fulltextColumns
     *
     * @return void
     */
    public function createTableFromSelect(
        Select $select,
        $tablePostfix,
        int $storeId,
        $engine = 'InnoDB',
        array $fulltextColumns = []
    ) {
        $tablePostfix = $this->helper->getIndexTablePostfix($tablePostfix, $storeId);
        $dataObject = new \Magento\Framework\DataObject(
            [
                self::SELECT => $select,
                self::TABLE_POSTFIX => $tablePostfix,
                self::ENGINE => $engine,
                self::FULTEXT_COLUMNS => $fulltextColumns,
            ]
        );

        $this->eventManager->dispatch(
            'create_abstract_entity_index_table_before',
            [
                'object' => $dataObject,
            ]
        );

        $select = $dataObject->getData(self::SELECT);
        $tablePostfix = $dataObject->getData(self::TABLE_POSTFIX);
        $engine = $dataObject->getData(self::ENGINE);
        $fulltextColumns = $dataObject->getData(self::FULTEXT_COLUMNS);

        $connection = $this->getConnection();
        $table = $this->getTable(self::TABLE_NAME) . $tablePostfix;
        $connection->dropTable($table);
        $connection->query("CREATE TABLE $table ENGINE=$engine $select");
        $columnsInTable = array_keys($connection->describeTable($table));
        $fulltextColumns = array_intersect($fulltextColumns, $columnsInTable);
        if (!empty($fulltextColumns)) {
            $connection->query("ALTER TABLE $table ADD FULLTEXT (" . implode(', ', $fulltextColumns) . ")");
        }
    }

    /**
     * @param string $entityName
     * @param string|null $searchTerm
     * @param array $attributes
     * @param array $sortOrder
     * @param bool $returnSelect
     * @param bool $completeCoincidence
     * @return array|Select
     */
    public function searchEntities(
        $entityName,
        $searchTerm = null,
        array $attributes = [],
        $sortOrder = [],
        $returnSelect = false,
        $completeCoincidence = false
    ) {
        $tablePostfix = $this->helper->getIndexTablePostfix($entityName);
        $connection = $this->getConnection();
        $table = $this->getTable(self::TABLE_NAME . $tablePostfix);
        if (!$connection->getTables($table)) {
            return [];
        }

        $query = $connection->select()->from($table);
        if (!empty($attributes) && $searchTerm !== null) {
            if ($completeCoincidence) {
                $query = $this->_completeCoincidenceSearch($query, $attributes, $searchTerm);
            } else {
                $query = $this->_fullTextSearch($query, $attributes, $searchTerm);
            }
        }

        if (!empty($sortOrder)) {
            $query->order($sortOrder);
        }

        if ($returnSelect) {
            return $query;
        }

        return $connection->fetchAll($query);
    }

    /**
     * @param AbstractEntityInterface $ae
     * @param strng $entityName
     * @param int $storeId
     * @param array $arguments
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function updateEntityIndexData(AbstractEntityInterface $ae, $entityName, int $storeId, $arguments = [])
    {
        $tablePostfix = $this->helper->getIndexTablePostfix($entityName, $storeId);
        $connection = $this->getConnection();
        $table = $this->getTable(self::TABLE_NAME . $tablePostfix);
        if (!$connection->getTables($table)) {
            return;
        }
        $where = ['entity_id = ?' => $ae->getId()];
        $connection->delete($table, $where);
        if (!empty($arguments['delete'])) {
            return;
        }

        $columns = $connection->describeTable($table);
        $data = $ae->getData();
        $insertData = [];
        foreach ($columns as $columnn => $columnData) {
            if (isset($data[$columnn])) {
                $insertData[$columnn] = $data[$columnn];
            }
        }
        $connection->insert($table, $insertData);
        return;
    }

    /**
     * @param Select $query
     * @param array $attributes
     * @param string $searchTerm
     * @return Select
     */
    protected function _fullTextSearch($query, $attributes, $searchTerm)
    {
        $query->where(
            'MATCH (' . implode(', ', $attributes) . ') AGAINST (? IN BOOLEAN MODE)',
            $this->config->getFulltextSearchValue($searchTerm)
        );

        foreach ($this->rangeAttributes as $range) {
            if (in_array($range, $attributes)) {
                $from = 'SUBSTRING_INDEX(' . $range . ', "-", 1)';
                $to = 'SUBSTRING_INDEX(' . $range . ', "-", -1)';
                $query->orWhere('? BETWEEN ' . $from . ' AND ' . $to, $searchTerm);
            }
        }

        return $query;
    }

    /**
     * @param Select $query
     * @param array $attributes
     * @param string $searchTerm
     * @return Select
     */
    protected function _completeCoincidenceSearch($query, $attributes, $searchTerm)
    {
        $customCondition = false;
        foreach ($attributes as $attribute) {
            if (!$customCondition) {
                $customCondition = $attribute . '=?';
            } else {
                $customCondition .= ' OR ' . $attribute . '=?';
            }
        }
        if ($customCondition) {
            $query->where($customCondition, $searchTerm);
        }
        return $query;
    }
}
