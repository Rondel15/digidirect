<?php

namespace Digidirect\MyStoreWidget\Model\ResourceModel;

use Digidirect\MyStoreWidget\Helper\Config;
use Digidirect\AbstractEntity\Helper\Config as AeConfigHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store;

/**
 * Class MyStoreIndex
 *
 * @package Digidirect\MyStoreWidget\Model\ResourceModel
 */
class MyStoreIndex extends AbstractDb
{
    const TABLE_NAME = 'digidirect_mystorewidget_store_index';

    /**
     * @var array
     */
    protected $rangeAttributes = [];

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * @var AeConfigHelper
     */
    protected $aeConfigHelper;

    /**
     * @var \Digidirect\MyStoreWidget\Helper\SearchHelper
     */
    protected $searchHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * MyStoreIndex constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Config $configHelper
     * @param \Digidirect\MyStoreWidget\Helper\SearchHelper $searchHelper
     * @param string|null $connectionName
     * @param array $rangeAttributes
     * @param StoreManagerInterface $storeManager
     * @param AeConfigHelper $aeConfigHelper
     */
    public function __construct(
        Context $context,
        Config $configHelper,
        \Digidirect\MyStoreWidget\Helper\SearchHelper $searchHelper,
        $connectionName = null,
        $rangeAttributes = ['postcode'],
        StoreManagerInterface $storeManager = null,
        AeConfigHelper $aeConfigHelper = null
    ) {
        parent::__construct($context, $connectionName);
        $this->rangeAttributes = $rangeAttributes;
        $this->configHelper = $configHelper;
        $this->searchHelper = $searchHelper;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $this->aeConfigHelper = $aeConfigHelper ?: ObjectManager::getInstance()->get(AeConfigHelper::class);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, 'entity_id');
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getTableName($storeId = Store::DEFAULT_STORE_ID)
    {
        return $this->getTable(self::TABLE_NAME . '_' . $storeId);
    }

    /**
     * @param Select $select
     * @param array $fulltextColumns
     * @param int $storeId
     * @return void
     */
    public function createTableFromSelect(
        Select $select,
        array $fulltextColumns = [],
        $storeId = Store::DEFAULT_STORE_ID
    ) {
        if ($this->configHelper->isSearchTypeTextInput()) {
            $fulltextColumns = array_diff($fulltextColumns, $this->rangeAttributes);
        }
        $connection = $this->getConnection();
        $table = $this->getTableName($storeId);
        $connection->dropTable($table);
        $connection->query("CREATE TABLE $table ENGINE=InnoDB $select");
        $tableStructure = $connection->describeTable($table);

        $columns = $this->getFulltextColumns($fulltextColumns, $storeId);

        //max 16 columns allowed for indices, @see https://dev.mysql.com/doc/refman/5.7/en/multiple-column-indexes.html
        if (count($columns) > 16) {
            throw new LocalizedException(
                __('You cannot specify more than 16 search attributes to use as searchable fields.')
            );
        }

        if (!empty($columns)) {
            $connection->query("ALTER TABLE $table ADD FULLTEXT (" . implode(', ', $columns) . ")");
        }

        foreach ($this->rangeAttributes as $rangeAttribute) {
            if (isset($tableStructure[$rangeAttribute])) {
                $connection->query("ALTER TABLE $table ADD INDEX ($rangeAttribute)");
            }
        }
    }

    /**
     * @param string|integer|null $searchTerm
     * @param array $attributes
     * @param int|null $storeId
     * @return array
     * @throws \Zend_Db_Statement_Exception
     */
    public function searchStores($searchTerm = null, array $attributes = [], $storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $connection = $this->getConnection();
        $table = $this->getTableName($storeId);

        $attributes = $this->getFulltextColumns($attributes, $storeId);

        if (!$connection->query("SHOW TABLES LIKE '$table';")->rowCount()) {
            return [];
        }
        $query = $connection->select()->from($table, ['*', 'type' => '(\'\')']);
        if (!empty($attributes) && $searchTerm !== null) {
            if ($this->configHelper->isSearchTypeTextInput()) {
                $attributes = array_diff($attributes, $this->rangeAttributes);
            }
            foreach ($this->rangeAttributes as $range) {
                if (in_array($range, $attributes)) {
                    continue;
                }
                $postcode = (int)$searchTerm;
                if ($postcode) {
                    $from = 'SUBSTRING_INDEX(' . $range . ', "-", 1)';
                    $to = 'SUBSTRING_INDEX(' . $range . ', "-", -1)';
                    $query->orWhere('? BETWEEN ' . $from . ' AND ' . $to, (int)$searchTerm);
                }
            }
            if ($attributes) {
                $query->orWhere(
                    'MATCH (' . implode(', ', $attributes) . ') AGAINST (? IN BOOLEAN MODE)',
                    $this->aeConfigHelper->getFulltextSearchValue($searchTerm, $storeId)
                );
            }
        }

        return $connection->fetchAll($query);
    }

    /**
     * @param array $attributes
     * @param int $storeId
     * @return array
     */
    protected function getFulltextColumns(array $attributes, $storeId)
    {
        $connection = $this->getConnection();
        $table = $this->getTableName($storeId);

        $tableStructure = $connection->describeTable($table);
        $columns = [];
        foreach ($attributes as $attribute) {
            if (isset($tableStructure[$attribute])
                && in_array($tableStructure[$attribute]['DATA_TYPE'], ['varchar', 'text'])
            ) {
                $columns[] = $attribute;
            }
        }

        return $columns;
    }
}
