<?php
namespace Digidirect\SEO\Model;

use Digidirect\SEO\Api\Data\SitemapExcludeInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class SitemapExclude
 * @package Digidirect\SEO\Model
 */
class SitemapExclude extends AbstractModel implements SitemapExcludeInterface
{
    /**#@+
     * Constants for item types
     */
    const ITEM_TYPE_CATEGORY = 'category';
    /**#@-*/

    /**#@+
     * Constants for status
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    /**#@-*/

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Digidirect\SEO\Model\ResourceModel\SitemapExclude');
    }

    /**
     * {@inheritdoc}
     */
    public function getExcludeId()
    {
        return $this->getData(self::EXCLUDE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setExcludeId($excludeId)
    {
        return $this->setData(self::EXCLUDE_ID, $excludeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemType()
    {
        return $this->getData(self::ITEM_TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemType($itemType)
    {
        return $this->setData(self::ITEM_TYPE, $itemType);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreId()
    {
        return $this->getData(self::STORE_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setStoreId($storeId)
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
