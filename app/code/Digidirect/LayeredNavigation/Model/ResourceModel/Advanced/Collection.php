<?php
namespace Digidirect\LayeredNavigation\Model\ResourceModel\Advanced;

/**
 * Class Collection
 * @package Digidirect\LayeredNavigation\Model\ResourceModel\Advanced
 */
class Collection extends \Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection
{
    /**
     * {@inheritdoc}
     */
    public function getSelectCountSql()
    {
        return $this->_getSelectCountSqlEvent();
    }

    /**
     * {@inheritdoc}
     */
    protected function _getSelectCountSqlEvent($select = null, $resetLeftJoins = true)
    {
        $countSelect = parent::_getSelectCountSql($select, $resetLeftJoins);
        $this->_eventManager->dispatch(
            'catalog_product_collection_prepare_select_count_sql_after',
            [
                'collection' => $this,
                'select' => $select,
                'count_select' => $countSelect,
            ]
        );
        return $countSelect;
    }
}
