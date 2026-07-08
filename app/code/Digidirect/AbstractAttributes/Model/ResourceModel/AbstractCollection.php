<?php
namespace Digidirect\AbstractAttributes\Model\ResourceModel;

use Magento\Store\Model\Store;

/**
 * Class AbstractCollection
 * @package Digidirect\AbstractAttributes\Model\ResourceModel
 */
class AbstractCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idEntityKey;

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var array
     */
    protected $_storeDataCache = [];

    /**
     * Add filter by store
     *
     * @param int|Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if ($store instanceof Store) {
            $store = $store->getId();
        }

        $this->_storeId = $store;
        $store = [$store];

        if ($withAdmin) {
            $store[] = Store::DEFAULT_STORE_ID;
        }

        $this->addFilter('main_table.store_id', ['in' => $store], 'public');
        return $this;
    }

    /**
     * Add field filter to collection
     *
     * @see self::_getConditionSql for $condition
     *
     * @param string|array $field
     * @param null|string|array $condition
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if (is_array($field) && in_array('store_id', $field) || $field == 'store_id') {
            return $this->addStoreFilter($condition);
        }
        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * @return $this
     */
    protected function _beforeLoad()
    {
        if ($this->_storeId === null) {
            $this->addStoreFilter(Store::DEFAULT_STORE_ID);
        }

        if ($this->_storeId && $this->_idEntityKey !== null) {
            $subQuery = $this->getConnection()
                ->select()
                ->from($this->getMainTable(), $this->_idEntityKey)
                ->where('store_id = ?', $this->_storeId);

            $storeWhere = new \Zend_Db_Expr('main_table.store_id = ' . $this->_storeId);
            $entityKeyWhere = new \Zend_Db_Expr('main_table.' . $this->_idEntityKey . ' NOT IN ?');
            $this->getSelect()
                ->where($storeWhere . ' OR ' . $entityKeyWhere, $subQuery)
                ->group('main_table.' . $this->_idEntityKey);
        }

        return parent::_beforeLoad();
    }
}
