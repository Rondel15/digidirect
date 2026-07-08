<?php
namespace Digidirect\SEO\Model\ResourceModel;

use Digidirect\SEO\Model\SitemapExclude as SitemapExcludeModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Store\Model\Store;

/**
 * Sitemap exclude model
 */
class SitemapExclude extends AbstractDb
{
    /**
     * Table name
     */
    const TABLE_NAME = 'digidirect_seo_sitemap_excluded_items';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, SitemapExcludeModel::EXCLUDE_ID);
    }

    /**
     * Check if exclude for provided item exists
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return bool
     */
    public function isExcluded($itemType, $itemId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), [SitemapExcludeModel::STATUS])
            ->where(SitemapExcludeModel::ITEM_TYPE . ' = ?', $itemType)
            ->where(SitemapExcludeModel::ITEM_ID . ' = ?', $itemId)
            ->where(SitemapExcludeModel::STORE_ID . ' = ?', $storeId);
        $status = $connection->fetchOne($select);
        if ($status !== false) {
            return (boolean)$status;
        }
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where(SitemapExcludeModel::ITEM_TYPE . ' = ?', $itemType)
            ->where(SitemapExcludeModel::ITEM_ID . ' = ?', $itemId)
            ->where(SitemapExcludeModel::STORE_ID . ' = ?', Store::DEFAULT_STORE_ID);
        return (boolean)$connection->fetchOne($select);
    }

    /**
     * Check if default store setting for sitemap exclude should be used.
     *
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return bool
     */
    public function isDefaultSetting($itemType, $itemId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where(SitemapExcludeModel::ITEM_TYPE . ' = ?', $itemType)
            ->where(SitemapExcludeModel::ITEM_ID . ' = ?', $itemId)
            ->where(SitemapExcludeModel::STORE_ID . ' = ?', $storeId);
        return !$connection->fetchOne($select);
    }

    /**
     * Load exclude by item ID
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param string $itemType
     * @param int $itemId
     * @param int $storeId
     * @return $this
     */
    public function loadByItemId(\Magento\Framework\Model\AbstractModel $object, $itemType, $itemId, $storeId)
    {
        $connection = $this->getConnection();
        if ($connection) {
            $select = $this->getConnection()
                ->select()
                ->from($this->getMainTable())
                ->where(SitemapExcludeModel::ITEM_TYPE . ' = ?', $itemType)
                ->where(SitemapExcludeModel::ITEM_ID . ' = ?', $itemId)
                ->where(SitemapExcludeModel::STORE_ID . ' = ?', $storeId);
            $data = $connection->fetchRow($select);

            if ($data) {
                $object->setData($data);
            }
        }
        $this->_afterLoad($object);

        return $this;
    }
}
