<?php
namespace Digidirect\SEO\Model\ResourceModel\SitemapExclude;

use Digidirect\SEO\Model\SitemapExclude;
use Magento\Store\Model\Store;

/**
 * Class Collection
 * @package Digidirect\SEO\Model\ResourceModel\SitemapExclude
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = SitemapExclude::EXCLUDE_ID;

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SitemapExclude::class, \Digidirect\SEO\Model\ResourceModel\SitemapExclude::class);
    }

    /**
     * Get excluded item IDs for provided store
     *
     * @param string $itemType
     * @param int $storeId
     * @return array
     */
    public function getExcludedItemIds($itemType, $storeId)
    {
        $mainTable = 'main_table';
        $disabledExcl = 'disabled_exclude';
        $itemTypeCol = SitemapExclude::ITEM_TYPE;
        $itemIdCol = SitemapExclude::ITEM_ID;
        $storeIdCol = SitemapExclude::STORE_ID;
        $statusCol = SitemapExclude::STATUS;
        $excludeIdCol = SitemapExclude::EXCLUDE_ID;
        $conn = $this->getConnection();

        $joinCond = "{$mainTable}.{$itemTypeCol} = {$disabledExcl}.{$itemTypeCol} AND " .
            "{$mainTable}.{$itemIdCol} = {$disabledExcl}.{$itemIdCol} AND " .
            "{$disabledExcl}.{$storeIdCol} = {$conn->quote($storeId)} AND " .
            "{$disabledExcl}.{$statusCol} = {$conn->quote(SitemapExclude::STATUS_DISABLED)}"
        ;

        $this->getSelect()
            ->joinLeft([$disabledExcl => $this->getMainTable()], $joinCond, '')
            ->where("{$mainTable}.{$itemTypeCol} = ?", $itemType)
            ->where("{$mainTable}.{$statusCol} = ?", SitemapExclude::STATUS_ENABLED)
            ->where("{$mainTable}.{$storeIdCol} in (?)", [(int)$storeId, Store::DEFAULT_STORE_ID])
            ->where("{$disabledExcl}.{$excludeIdCol} is NULL")
        ;
        return $this->getColumnValues($itemIdCol);
    }
}
