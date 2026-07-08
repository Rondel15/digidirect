<?php
namespace Digidirect\Blog\Model\ResourceModel\Product;

/**
 * Class Collection
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * @param null $select
     * @return \Magento\Framework\DB\Select|null
     */
    protected function _buildClearSelect($select = null)
    {
        $select = parent::_buildClearSelect();
        $select->reset(\Magento\Framework\DB\Select::GROUP);
        return $select;
    }
}
